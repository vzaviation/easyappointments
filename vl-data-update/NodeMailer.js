'use strict'

// npm install @aws-sdk/client-ses nodemailer

import nodemailer from 'nodemailer';
import {Utility} from "./Utility.js";

export class NodeMailer {

    constructor() {
    }

    // Send e-mail using SMTP
    createTransport = (() => {
        return nodemailer.createTransport({
            host: process.env.SMTP_HOST,
            port: process.env.SMTP_PORT,
            secure: process.env.SMTP_AUTH,
            auth: {
                user: process.env.SMTP_USER,
                pass: process.env.SMTP_PASS,
            },
            tls: {
                ciphers:'SSLv3'
            },
            debug: true
        });
    });

/* *******************************************
 *  Built-in Nodemailer AWS SES method - does not work without separate IAM setup
    ses = () => {
        return new aws.SES({
            apiVersion: "2010-12-01",
            region: process.env.AWS_REGION,
            credentials: {
                secretAccessKey: process.env.AWS_SECRET_ACCESS_KEY,
                accessKeyId: process.env.AWS_ACCESS_KEY_ID
            }
        });
    }

    // create Nodemailer SES transporter
    createTransport = () => {
        const SES = this.ses;
        return nodemailer.createTransport({
            SES: { SES, aws },
        });
    }
 */

    // send some mail
    sendMail = async (sendfrom, sendto, subject, message, filename, path) => {
        try {
            const transport = this.createTransport();
            if ( (Utility.isNotEmpty(filename)) && (Utility.isNotEmpty(path)) ) {
                await transport.sendMail(
                    {
                        from: sendfrom,
                        to: sendto,
                        subject: subject,
                        text: message,
                        attachments: [{
                            filename: filename,
                            path: path
                        }],
                        ses: {
                            // optional extra arguments for SendRawEmail
                        }
                    },
                    (err, info) => {
                        if (err) {
                            throw new Error(err);
                        } else {
                            console.log(info.envelope);
                            console.log(info.messageId);
                        }
                    }
                );
            } else {
                await transport.sendMail(
                    {
                        from: sendfrom,
                        to: sendto,
                        subject: subject,
                        text: message,
                        ses: {
                            // optional extra arguments for SendRawEmail
                        }
                    },
                    (err, info) => {
                        if (err) {
                            throw new Error(err);
                        } else {
                            console.log(info.envelope);
                            console.log(info.messageId);
                        }
                    }
                );
            }
            return "NodeMailer sendMail: SUCCESS";
        } catch (err) {
            return "NodeMailer sendMail: ERROR " + err;
        }
    }
}
