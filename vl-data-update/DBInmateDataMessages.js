'use strict'
import * as sql from 'mysql2/promise';
import moment from 'moment';
import { Utility }  from './Utility.js';

/*
 *  CRUD ops on the ea_inmates table
 */
export class DBInmateDataMessages {

    dbConfig = {
        host: process.env.DBHOST,
        user: process.env.DBUSER,
        password: process.env.DBPASSWORD,
        database: process.env.DATABASE
    };

    inmate_messages_table = "VisitationLink.ea_inmate_data_messages";
    user_messages_table = "VisitationLink.user_message_status";

    writerLog = {};

    constructor(writerLog) {
        this.writerLog = writerLog
//        this.dbConfig.authentication.options.password = pword
    }
    
    eaInmateDataMessageRow = (inData) => {
        let outData = new Object(inData);
        outData.id = Utility.cleanVal(inData.id)
        outData.update_datetime = Utility.cleanVal(inData.update_datetime)
        outData.so_num = Utility.cleanVal(inData.so_num)
        outData.message = Utility.cleanVal(inData.message)

        return outData;
    }

    eaUserMessageStatusRow = (inData) => {
        let outData = new Object(inData);
        outData.id = Utility.cleanVal(inData.id)
        outData.user_id = Utility.cleanVal(inData.user_id)
        outData.inmate_message_unread_list = Utility.cleanVal(inData.inmate_message_unread_list)

        return outData;
    }

    getAllMessages = async () => {
        const query = "SELECT " +
            "id, " +
            "DATE_FORMAT(update_datetime, '%Y-%m-%d %T') as 'update_datetime', " +
            "inmate_so_num as 'so_num', " +
            "message " +
            "FROM " + this.inmate_messages_table;

        let results = new Map();

        let conn
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query)
            // rows[0] contains the result set
            // loop over each row to transpose into individual record rows
            Utility.Log(this.writerLog, " === === " + rows[0].length + " rows fetched from table");
            for (const dbRow of rows[0]) {
                let row = this.eaInmateDataMessageRow(dbRow);
                if ((Utility.isNotEmpty(row.id)) && !isNaN(row.id)) {
                    const key = row.id;
                    let outputArray = [];

                    if (!results.has(key)) {
                        outputArray.push(row);
                        results.set(key, outputArray)
                    } else {
                        // Existing ID? weird - ignore
                    }
                }
            }
            return [results];
        } catch (dbErr) {
            Utility.Log(this.writerLog,dbErr);
            throw dbErr;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }

    bulkInsertInmateDataMessages = async (dbObjArr) => {
        const query = "INSERT INTO " + this.inmate_messages_table +
            " (update_datetime, " +
            " inmate_so_num, " +
            " message) " +
            " VALUES ? ";

        let conn
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query,[dbObjArr]);
            // rows[0] contains the result set (created ID)
            //Utility.Log(this.writerLog, " === === Records inserted ");
            return rows[0];
        } catch (dbErr) {
            Utility.Log(this.writerLog, dbErr);
        } finally {
            if (conn && conn.end) conn.end();
        }
    }
}
