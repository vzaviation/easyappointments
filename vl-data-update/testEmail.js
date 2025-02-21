'use strict'

import dotenv from 'dotenv'
dotenv.config()

import fs from 'fs';
import moment from 'moment';
import util, { promisify } from 'util';

import { NodeMailer } from './NodeMailer.js';
import { Utility }  from './Utility.js';
import {FTPOps} from "./FTPOps.js";

/* ***** Global Init ***** */
const now = moment(new Date()).format('YYYYMMDD-HHmmss');

const sendTestEmail = async () => {
    const sendFrom = "noreply@visitationlink.com";
    const sendTo = "karlpbuchmann@gmail.com";
    const subject = "Test Message from VisitationLink Scripting";
    const message = "This is a test message sent at: " + now;
    const filename = "processed-files.txt";
    const path = "./processed-files.txt";

    const mailer = new NodeMailer();
    const result = await mailer.sendMail(sendFrom, sendTo, subject, message, filename, path);

    return result;
}

// Run it
sendTestEmail()
    .then((result) => {
        console.log("Finished: " + JSON.stringify(result));
    });
