'use strict'

/*
 * VL-update-inmate-data
 *
 * Karl Buchmann
 *
 * Script to update VisitationLink inmate data from API drop
 *  1. Retrieve file from FTP server
 *  2. Read file
 *  3. Read cell lookup information
 *  4. Update DB ea_inmates table with latest info
 *
 */
import dotenv from 'dotenv'
dotenv.config()

import fs from 'fs';
import moment from 'moment';
import util, { promisify } from 'util';

import { CellLookupData } from './CellLookupData.js';
import { DBEAInmates } from './DBEAInmates.js';
import { FTPOps } from './FTPOps.js';
import { InmateDataIn } from './InmateDataIn.js';
import { ProcessedFile } from './ProcessedFile.js';
import { Utility }  from './Utility.js';
import {DBInmateDataMessages} from "./DBInmateDataMessages.js";
import {NodeMailer} from "./NodeMailer.js";

/* ***** Global Init ***** */
const mv = util.promisify(fs.rename);

const now = moment(new Date()).format('YYYYMMDD-HHmmss');
const logFileName = "VL-update-inmate-data_" + now + ".log";
const logFilePath = "logs/" + logFileName;
let writerLog = fs.createWriteStream(logFilePath, {flags: 'w+'});

// Open the processed-files file for update as files are processed
let procAppender = fs.createWriteStream("processed-files.txt", {flags: 'a'});

/*
 * *******************************************
 * *******************************************
 *   START Main functional block
 * *******************************************
 * *******************************************
 */
const formatMessage = (inmate_name, inmate_so, message) => {
    return "Inmate: " + inmate_name + " (" + inmate_so + ") " + message;
};

/*
 * Read the cell lookup file and get List of Files to Download
 */
const getLatestUnprocessedFilesList = async () => {
    // LOGIC:
    //  - Read in the cell lookup reference data
    //  - Get a list of files that have already been processed
    //  - Retrieve a list of files from the server
    //  - Retrieve the inmates table from the DB before processing for later comparison
    //  - Compare and download any files that have not yet been processed
    try {
        const cellLookup = new CellLookupData("WallerCtyTX-CellLookup.csv");
        const [cellCount, cellLookupMap] = await cellLookup.readAndParse();
        Utility.Log(writerLog,"*** MAIN: Cell Lookup data loaded");

        const procFile = new ProcessedFile("processed-files.txt");
        const [procCount, procFileList] = await procFile.readAndParse();
        Utility.Log(writerLog, "*** MAIN: Processed File List Retrieved: " + procFileList.length + " files");

        // Get list of files on FTP server
        const ftp = new FTPOps(writerLog);
        const ftpList = await ftp.getFileList("")
        Utility.Log(writerLog, "*** MAIN: List of Files on FTP Server Retrieved: " + ftpList.length + " files");

        // Sort the filenames
        ftpList.sort((a,b) => (a.name > b.name) ? 1 : ((b.name > a.name) ? -1 : 0));

        // Compare the two and save any file that is on the ftpList
        //  but not the processed list
        let filesToDownloadList = new Array();
        for (let i = 0; i < ftpList.length; i++) {
            const aFile = ftpList[i].name;
            if (!procFileList.includes(aFile)) {
                if (!aFile.startsWith(".")) {
                    filesToDownloadList.push(aFile);
                }
            }
        }

        return {cellLookupMap, filesToDownloadList};
    } catch (err) {
        Utility.Log(writerLog,err)
        return -1;
    }
}

/*
 * Process an inmate file
 *  - Retrieve from server
 *  - read and update the database table
 *  - move file to processed dir
 */
const processInmateFile = async (cellLookupMap, filename) => {
    let updateList = [];
    let updateMap = new Map();
    let insertList = [];
    try {
        // Get the file and put it into inmate_files dir
        const ftp = new FTPOps(writerLog);
        await ftp.getFile(filename, "");

        // Read the file
        const inmateFile = new InmateDataIn("inmate-files/" + filename);
        const [recordCount, inmateFileMap] = await inmateFile.readAndParse();
        // Some files are in the wrong format (so recordCount will be 0) and will be skipped
        if (recordCount > 0) {
            Utility.Log(writerLog, "*** MAIN: Loaded file: " + filename);

            // Zero out the booking_status for any current records
            const dbproc = new DBEAInmates(writerLog);
            const curInmateData = await dbproc.getAllRecords();
            const rows = await dbproc.zeroBookingStatusAll();

            // Next:
            //  - Loop through the data
            //  - Compare the current record to the existing database
            //  - Note any important differences
            //  - Create arrays for batch updating / inserting as appropriate
            const inmateSoList = Array.from(inmateFileMap.keys());
            Utility.Log(writerLog, "*** MAIN: " + inmateSoList.length + " inmates loaded from file: " + filename);
            // Parse filename for update date
            const filenameRegex = /.*-(\d{4})(\d{2})(\d{2})_(\d{2})(\d{2})(\d{2}).csv/gm;
            const dtArr = filenameRegex.exec(filename);
            const fileDateTimeStr = dtArr[1] + "-" + dtArr[2] + "-" + dtArr[3] + " " + dtArr[4] + ":" + dtArr[5] + ":" + dtArr[6] + ".000+00:00";
            const fileDateTime = new Date(fileDateTimeStr);

            for (const so_num of inmateSoList) {
                const existingInmateRow = curInmateData.get(so_num);
                const inmateRow = inmateFileMap.get(so_num);
                if (Utility.isNotEmpty(inmateRow)) {
                    // Create the inmate name
                    let inmateFullName = inmateRow.first_name;
                    if (Utility.isNotEmpty(inmateRow.middle_name)) {
                        inmateFullName += " " + inmateRow.middle_name;
                    }
                    inmateFullName += " " + inmateRow.last_name;
                    // Get the classification level / cell lookup - if null, default to 99
                    let classLevel = cellLookupMap.get(Utility.toLowerCase(inmateRow.cell_ref)) != null ? cellLookupMap.get(Utility.toLowerCase(inmateRow.cell_ref)) :"99";
                    // Build the DB row from the input data
                    let dbObj = {
                        "booking": "",
                        "so_num": inmateRow.so_num,
                        "inmate_name": inmateFullName,
                        "dob": inmateRow.dob,
                        "dl": "",
                        "booking_date": inmateRow.book_date,
                        "release_date": "",
                        "inmate_flag": "",
                        "number_visits_7": 0,
                        "inmate_classification_level": classLevel,
                        "gender": inmateRow.gender,
                        "cell": inmateRow.cell_ref,
                        "last_updated_file": filename
                    };
                    let dbArr = [
                        "",
                        inmateRow.so_num,
                        inmateFullName,
                        inmateRow.dob,
                        "",
                        inmateRow.book_date,
                        "",
                        "",
                        1,
                        0,
                        classLevel,
                        inmateRow.gender,
                        inmateRow.cell_ref,
                        filename
                    ]
                    // Update or insert?
                    if (Utility.isNotEmpty(existingInmateRow)) {
                        updateList.push(dbObj);
                        updateMap.set(dbObj.so_num,dbObj);
                    } else {
                        insertList.push(dbArr);
                    }
                }
            }
            // Perform batch updates and inserts
            const updateCnt = await dbproc.bulkUpdateEAInmatesTable(updateList);
            if (updateCnt === -1) {
                // error processing updates
                return -3;
            } else {
                // Proceed with inserts as well
                const insertCnt = await dbproc.bulkInsertEAInmatesTable(insertList);
                if (insertCnt === -1) {
                    // error processing inserts
                    return -4;
                } else {
                    // Now process the differences to update user messages
                    //  Loop through the current inmate records where booking_status = 1
                    //  If the update records do not have a particular inmate, that means the booking status changes to 0 and they are now unhoused
                    //  Note that condition
                    let messageArr = [];
//                    const now = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
                    const fileTime = moment(fileDateTime).format('YYYY-MM-DD HH:mm:ss');
                    const curInmateKeys = Array.from(curInmateData.keys());
                    for (const curInmateSo of curInmateKeys) {
                        const curInmate = curInmateData.get(curInmateSo);
                        if (curInmate.booking_status == 1) {    //  Currently housed
                            const updObj = updateMap.get(curInmate.so_num);
                            if (Utility.isNotEmpty(updObj)) {
                                let messageObjArr = [];
                                messageObjArr[0] = fileTime;
                                messageObjArr[1] = updObj.so_num;
                                // Compare cell, inmate_classification_level, booking_status
                                if (curInmate.cell !== updObj.cell) {
                                    messageObjArr[2] = formatMessage(curInmate.inmate_name, curInmate.so_num, "cell changed from " + curInmate.cell + " to " + updObj.cell);
                                    messageArr.push(messageObjArr);
                                }
                            } else {
                                let messageObjArr = [];
                                messageObjArr[0] = fileTime;
                                messageObjArr[1] = curInmate.so_num;
                                messageObjArr[2] = formatMessage(curInmate.inmate_name, curInmate.so_num, "is no longer housed");
                                messageArr.push(messageObjArr);
                            }
                        } else {   //  Currently unhoused - are they back in the joint?
                            const updObj = updateMap.get(curInmate.so_num);
                            if (Utility.isNotEmpty(updObj)) {
                                let messageObjArr = [];
                                messageObjArr[0] = fileTime;
                                messageObjArr[1] = updObj.so_num;
                                messageObjArr[2] = formatMessage(curInmate.inmate_name, curInmate.so_num, "was unhoused and is now housed again");
                                messageArr.push(messageObjArr);
                            }
                        }
                    }
                    let messageObjArr = [];
                    messageObjArr[0] = fileTime;
                    for (const insArr of insertList) {
                        messageObjArr[1] = insArr[1];
                        messageObjArr[2] = "Inmate: " + insArr[2] + " / " + insArr[1] + " || New As Of " + fileTime;
                        messageArr.push(messageObjArr);
                    }
                    // Write messages to DB
                    if (messageArr.length > 0) {
                        const dbmessages = new DBInmateDataMessages(writerLog);
                        const result = await dbmessages.bulkInsertInmateDataMessages(messageArr);
                    }

                    // All done
                    Utility.Log(writerLog, "*** MAIN: Processing Successful for: " + filename);
                    return (updateCnt + insertCnt);
                }
            }
        } else {
            Utility.Log(writerLog, "*** MAIN: Skipping file: " + filename + " - Format Error");
            return -1;
        }
    } catch (err) {
        Utility.Log(writerLog,"*** MAIN: ERROR: " + err)
        return -2;
    } finally {
        // Update the processed-files list and remove the files
        procAppender.write(filename + "\n");
        await fs.unlinkSync("./inmate-files/" + filename);
    }
}

const sendEmail = async (filename, path) => {
    const sendFrom = "noreply@visitationlink.com";
    const sendTo = "karlpbuchmann@gmail.com";
    const subject = "VisitationLink Scripting: Inmate data files processed";
    const message = "Processing file log attached. Message sent at: " + now;

    const mailer = new NodeMailer();
    const result = await mailer.sendMail(sendFrom, sendTo, subject, message, filename, path);

    return result;
}

/*
 * *******************************************
 *   END Main functional block
 * *******************************************
 */

/*
 * *******************************************
 *   START Run block
 * *******************************************
 */
// Run it
Utility.Log(writerLog," *** BEGINNING PROCESSING: " + Utility.curtime() + " ***");

getLatestUnprocessedFilesList()
    .then(async ({cellLookupMap, filesToDownloadList}) => {
        Utility.Log(writerLog, "MAIN: FILES to DOWNLOAD: " + filesToDownloadList.length);
        let validCnt = 0;
        let fmtCnt = 0;
        let errCnt = 0;
        for (let i = 0; i < filesToDownloadList.length; i++) {
            const filename = filesToDownloadList[i];
            const ret = await processInmateFile(cellLookupMap, filename);
            switch (ret) {
                case -1:
                    // File format error
                    fmtCnt++;
                    break;
                case -2:
                    // Some kind of exception
                    Utility.Log(writerLog, "General ERROR");
                    errCnt++;
                    break;
                case -3:
                    // Error on DB bulk update
                    Utility.Log(writerLog, "Bulk Update ERROR");
                    errCnt++;
                    break;
                case -4:
                    // Error on DB bulk insert
                    Utility.Log(writerLog, "Bulk Insert ERROR");
                    errCnt++;
                    break;
                default:
                    // All good - count of updates + inserts processed
                    validCnt++;
                    Utility.Log(writerLog, "File : " + filename + " - " + ret + " inmates processed");
                    break;
            }
        }
        Utility.Log(writerLog, "Total files processed: " + (validCnt + fmtCnt + errCnt));
        Utility.Log(writerLog, " --> Valid files  : " + validCnt);
        Utility.Log(writerLog, " --> Wrong format : " + fmtCnt);
        Utility.Log(writerLog, " --> Errors       : " + errCnt);

        Utility.Log(writerLog," *** FINISHED PROCESSING: " + Utility.curtime() + " ***");

        // Email the log to the admin email(s)
        await sendEmail(logFileName, logFilePath);
});    

/*
 * *******************************************
 *   END Run block
 * *******************************************
 */
