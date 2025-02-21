'use strict'

import pftp from 'promise-ftp';
import bftp from 'basic-ftp';

import fs from 'fs';
import { Utility }  from './Utility.js';

export class FTPOps {

    ftpConfig = {
        host: process.env.FTPHOST,
        user: process.env.FTPUSER,
        password: process.env.FTPPASSWORD,
        secure: false
    };

    writerlog = {};

    constructor(writerlog) {
        this.writerlog = writerlog;
    }

    getFileList = async (dir) => {
        try {
            let client = new bftp.Client();
            client.ftp.verbose = false;
            await client.access(this.ftpConfig);
            if ((dir != null) && (dir !== "")) {
                Utility.Log(this.writerlog,"*** FTP: Changing to DIR = " + dir);
                await client.cd(dir)
            }
            Utility.Log(this.writerlog,"*** FTP: Retrieving File List ...");
            const ftpList = await client.list();
            Utility.Log(this.writerlog,"*** FTP: File List Retrieved: " + ftpList.length + " files");
            Utility.Log(this.writerlog,"*** FTP: Closing Connection ");
            client.close();
            return ftpList;
        } catch (err) {
            Utility.Log(this.writerlog,"FTP list err: " + err);
        }
    }

    getFile = async (filename, dir) => {
        try {
            let ftp = new pftp();
            await ftp.connect(this.ftpConfig)
            .then(async (serverMessage) => {
                //Utility.Log(this.writerlog,"*** FTP: Server Message: " + serverMessage);
                if ((dir != null) && (dir != "")) {
                    Utility.Log(this.writerlog,"*** FTP: Changing to DIR = " + dir);
                    await ftp.cwd(dir)
                }
                Utility.Log(this.writerlog,"*** FTP: Retrieving File: " + filename);
                const stream = await ftp.get(filename);
                return stream;    
            })
            .then((stream) => {
                return new Promise(function (resolve, reject) {
                    stream.once('close', resolve);
                    stream.once('error', reject);
                    stream.pipe(fs.createWriteStream('inmate-files/' + filename));
                })
            })
            .then(() => {
                return ftp.end();
            });
        } catch (err) {
            Utility.Log(this.writerlog,"FTP get err: " + err);
        }
    }
}
