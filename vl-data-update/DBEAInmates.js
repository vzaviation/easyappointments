'use strict'
import * as sql from 'mysql2/promise';
import moment from 'moment';
import { Utility }  from './Utility.js';

/*
 *  CRUD ops on the ea_inmates table
 */
export class DBEAInmates {

    dbConfig = {
        host: process.env.DBHOST,
        user: process.env.DBUSER,
        password: process.env.DBPASSWORD,
        database: process.env.DATABASE
    };

    tablename = "VisitationLink.ea_inmates";

    writerLog = {};

    constructor(writerLog) {
        this.writerLog = writerLog
//        this.dbConfig.authentication.options.password = pword
    }
    
    eaInmatesRow = (inData) => {
        let outData = new Object(inData);
        outData.id = Utility.cleanVal(inData.id)
        outData.booking = Utility.cleanVal(inData.booking)
        outData.so_num = Utility.cleanVal(inData.so_num)
        outData.inmate_name = Utility.cleanVal(inData.inmate_name)
        outData.dob = Utility.cleanVal(inData.dob)
        outData.dl = Utility.cleanVal(inData.dl)
        outData.booking_date = Utility.cleanVal(inData.booking_date)
        outData.release_date = Utility.cleanVal(inData.release_date)
        outData.inmate_flag = Utility.cleanVal(inData.inmate_flag)
        outData.booking_status = Utility.cleanVal(inData.booking_status)
        outData.number_visits_7 = Utility.cleanVal(inData.number_visits_7)
        outData.inmate_classification_level = Utility.cleanVal(inData.inmate_classification_level)
        outData.gender = Utility.cleanVal(inData.gender)
        outData.cell = Utility.cleanVal(inData.cell)
        outData.last_updated_file = Utility.cleanVal(inData.last_updated_file)
        outData.last_updated_at = Utility.cleanVal(inData.last_updated_at)
        
        return outData;
    }

    getAllRecords = async () => {
        const query = "SELECT " +
            "ID as 'id', " +
            "Booking as 'booking', " +
            "SO as 'so_num', " +
            "inmate_name as 'inmate_name', " +
            "DOB as 'dob', " +
            "DL as 'dl', " +
            "Booking_Date as 'booking_date', " +
            "Release_Date as 'release_date', " +
            "inmate_flag as 'inmate_flag', " +
            "booking_status as 'booking_status', " +
            "number_visits_7 as 'number_visits_7', " +
            "inmate_classification_level as 'inmate_classification_level', " +
            "gender as 'gender', " +
            "cell as 'cell', " +
            "last_updated_file, " +
            "DATE_FORMAT(last_updated_at, '%Y-%m-%d %T') as 'last_updated_at'" +
            "FROM " + this.tablename + " ";

        let dataMap = new Map();

        let conn
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query)
            // rows[0] contains the result set
            // loop over each row to transpose into individual record rows
            Utility.Log(this.writerLog, " === === " + rows[0].length + " rows fetched from table");
            for (const dbRow of rows[0]) {
                let row = this.eaInmatesRow(dbRow);
                const key = row["so_num"];
                if (Utility.isNotEmpty(key)) {
                    if (!dataMap.has(key)) {
                        dataMap.set(key, row);
                    } else {
                        // Ignore duplicate rows
                    }
                }
            }
            return dataMap;
        } catch (dbErr) {
            Utility.Log(this.writerLog,dbErr);
            throw dbErr;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    getInmateBySO = async (so_num) => {
        const query = "SELECT " +
            "`ID` as 'id', " +
            "Booking as 'booking', " +
            "SO as 'so_num', " +
            "inmate_name as 'inmate_name', " +
            "DOB as 'dob', " +
            "DL as 'dl', " +
            "Booking_Date as 'booking_date', " +
            "Release_Date as 'release_date', " +
            "inmate_flag as 'inmate_flag', " +
            "booking_status as 'booking_status', " +
            "number_visits_7 as 'number_visits_7', " +
            "inmate_classification_level as 'inmate_classification_level', " +
            "gender as 'gender', " +
            "cell as 'cell', " +
            "last_updated_file, " +
            "DATE_FORMAT(last_updated_at, '%Y-%m-%d %T') as 'last_updated_at'" +
            "FROM " + this.tablename + " " +
            "WHERE SO = ? ";

        let results = new Map();

        let conn
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query, [so_num]);
            // rows[0] contains the result set
            return rows[0];
        } catch (dbErr) {
            Utility.Log(this.writerLog, dbErr);
            throw dbErr;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    zeroBookingStatusAll = async () => {
        const query = "UPDATE  " +
            this.tablename + " eai " +
            "set eai.booking_status = 0;";

        let results = new Array();

        let conn
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query);
            // rows[0] contains the result set
            return rows[0];
        } catch (dbErr) {
            Utility.Log(this.writerLog,dbErr);
            throw dbErr;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    upsertEAInmatesTable = async (dbObj) => {
        const queryI = "INSERT INTO " + this.tablename + " " +
            "(Booking, " +
            "SO, " +
            "inmate_name, " +
            "DOB, " +
            "DL, " +
            "Booking_Date, " +
            "Release_Date, " +
            "inmate_flag, " +
            "booking_status, " +
            "number_visits_7, " +
            "inmate_classification_level, " +
            "gender, " +
            "cell, " +
            "last_updated_file) " +
            "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?) ";

        const now = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
        const queryU = "UPDATE " + this.tablename + " " +
            "set Booking = ?, " +
            "SO = ?, " +
            "inmate_name = ?, " +
            "DOB = ?, " +
            "DL = ?, " +
            "Booking_Date = ?, " +
            "Release_Date = ?, " +
            "inmate_flag = ?, " +
            "booking_status = 1, " +
            "number_visits_7 = ?, " +
            "inmate_classification_level = ?, " +
            "gender = ?, " +
            "cell = ?, " +
            "last_updated_file = ?, " +
            "last_updated_at = '" + now + "' " +
            "WHERE SO = ? ";

        let conn
        let rows
        let operation = "UPDATE";
        try {
            // If inmate exists already, update, if not, insert
            const exists = await this.getInmateBySO(dbObj.so_num);
            if ((Utility.isNotEmpty(exists)) && (exists.length > 0)) {
                // Update
                //Utility.Log(this.writerLog, "upsertEAInmatesTable: updating existing record SO = " + dbObj.so_num);

                conn = await sql.createConnection(this.dbConfig);
                rows = await conn.query(queryU,
                [dbObj.booking,
                dbObj.so_num,
                dbObj.inmate_name,
                dbObj.dob,
                dbObj.dl,
                dbObj.booking_date,
                dbObj.release_date,
                dbObj.inmate_flag,
                dbObj.number_visits_7,
                dbObj.inmate_classification_level,
                dbObj.gender,
                dbObj.cell,
                dbObj.last_updated_file,
                dbObj.so_num]);
            } else {
                // Insert
                //Utility.Log(this.writerLog, "upsertEAInmatesTable: inserting record SO = " + dbObj.so_num);

                operation = "INSERT";
                conn = await sql.createConnection(this.dbConfig);
                rows = await conn.query(queryI,
                [dbObj.booking,
                dbObj.so_num,
                dbObj.inmate_name,
                dbObj.dob,
                dbObj.dl,
                dbObj.booking_date,
                dbObj.release_date,
                dbObj.inmate_flag,
                '1',
                dbObj.number_visits_7,
                dbObj.inmate_classification_level,
                dbObj.gender,
                dbObj.cell,
                dbObj.last_updated_file]);
            }

            // rows[0] contains the result set (created ID)
            return [operation, rows[0]];
        } catch (dbErr) {
            Utility.Log(this.writerLog, "upsertEAInmatesTable: ERROR: " + dbErr);
            //  Don't throw the error, let the records process through
            //throw dbErr;
            return ["ERROR", null]
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    bulkInsertEAInmatesTable = async (dbObjArr) => {
        const query = "INSERT INTO " + this.tablename + " " +
            "(Booking, " +
            "SO, " +
            "inmate_name, " +
            "DOB, " +
            "DL, " +
            "Booking_Date, " +
            "Release_Date, " +
            "inmate_flag, " +
            "booking_status, " +
            "number_visits_7, " +
            "inmate_classification_level, " +
            "gender, " +
            "cell, " +
            "last_updated_file) " +
            "VALUES ? ";

        let conn
        try {
            if (dbObjArr.length !== 0) {
                conn = await sql.createConnection(this.dbConfig);
                const rows = await conn.query(query, [dbObjArr]);
                // rows[0] contains the result set (created ID)
                //Utility.Log(this.writerLog, " === === Records inserted ");
                return rows[0].affectedRows;
            } else {
                return 0;
            }
        } catch (dbErr) {
            Utility.Log(this.writerLog, dbErr);
            return -1;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    updateEAInmatesTable = async (dbObj) => {
        const now = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
        const queryU = "UPDATE " + this.tablename + " " +
            "set Booking = ?, " +
            "SO = ?, " +
            "inmate_name = ?, " +
            "DOB = ?, " +
            "DL = ?, " +
            "Booking_Date = ?, " +
            "Release_Date = ?, " +
            "inmate_flag = ?, " +
            "booking_status = 1, " +
            "number_visits_7 = ?, " +
            "inmate_classification_level = ?, " +
            "gender = ?, " +
            "cell = ?, " +
            "last_updated_file = ?, " +
            "last_updated_at = '" + now + "' " +
            "WHERE SO = ? ";

        let conn
        let rows
        try {
            // Update
            conn = await sql.createConnection(this.dbConfig);
            rows = await conn.query(queryU,
                [dbObj.booking,
                    dbObj.so_num,
                    dbObj.inmate_name,
                    dbObj.dob,
                    dbObj.dl,
                    dbObj.booking_date,
                    dbObj.release_date,
                    dbObj.inmate_flag,
                    dbObj.number_visits_7,
                    dbObj.inmate_classification_level,
                    dbObj.gender,
                    dbObj.cell,
                    dbObj.last_updated_file,
                    dbObj.so_num]);
            // rows[0] contains the result set (created ID)
            return rows[0];
        } catch (dbErr) {
            Utility.Log(this.writerLog, "updateEAInmatesTable: ERROR: " + dbErr);
            //  Don't throw the error, let the records process through
            //throw dbErr;
            return -1;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    bulkUpdateEAInmatesTable = async (dbObjArr) => {
        const now = moment(new Date()).format('YYYY-MM-DD HH:mm:ss');
        const queryU = "UPDATE " + this.tablename + " " +
            "set Booking = ?, " +
            "SO = ?, " +
            "inmate_name = ?, " +
            "DOB = ?, " +
            "DL = ?, " +
            "Booking_Date = ?, " +
            "Release_Date = ?, " +
            "inmate_flag = ?, " +
            "booking_status = 1, " +
            "number_visits_7 = ?, " +
            "inmate_classification_level = ?, " +
            "gender = ?, " +
            "cell = ?, " +
            "last_updated_file = ?, " +
            "last_updated_at = '" + now + "' " +
            "WHERE SO = ? ";

        let conn
        let rows
        try {
            // Update
            let successCnt = 0;
            if (dbObjArr.length !== 0) {
                conn = await sql.createConnection(this.dbConfig);
                for (const dbObj of dbObjArr) {
                    if (dbObj.inmate_classification_level != null) {
                        rows = await conn.query(queryU,
                            [dbObj.booking,
                                dbObj.so_num,
                                dbObj.inmate_name,
                                dbObj.dob,
                                dbObj.dl,
                                dbObj.booking_date,
                                dbObj.release_date,
                                dbObj.inmate_flag,
                                dbObj.number_visits_7,
                                dbObj.inmate_classification_level,
                                dbObj.gender,
                                dbObj.cell,
                                dbObj.last_updated_file,
                                dbObj.so_num]);
                        // rows[0] contains the result set (created ID)
                        if (Utility.isNotEmpty(rows[0])) {
                            successCnt++;
                        }
                    } else {
                        console.log("Inmate Class Level NULL: " + JSON.stringify(dbObj));
                    }
                }
            }
            return successCnt;
        } catch (dbErr) {
            Utility.Log(this.writerLog, "updateEAInmatesTable: ERROR: " + dbErr);
            //  Don't throw the error, let it get handled so the file can be skipped
            //throw dbErr;
            return -1;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }
}
