'use strict'
import * as sql from 'mysql2/promise';
import moment from 'moment';
import { Utility }  from './Utility.js';

/*
 *  CRUD ops on the ea_inmates table
 */
export class DBCellLookupData {

    dbConfig = {
        host: process.env.DBHOST,
        port: process.env.DBPORT,
        user: process.env.DBUSER,
        password: process.env.DBPASSWORD,
        database: process.env.DATABASE
    };

    tablename = "ea_cell_lookup";

    writerLog = {};

    constructor(writerLog) {
        this.writerLog = writerLog
//        this.dbConfig.authentication.options.password = pword
    }
    
    eaCellLookupRow = (inData) => {
        let outData = new Object(inData);
        outData.cell_lookup_id = Utility.cleanVal(inData.cell_lookup_id)
        outData.cell = Utility.cleanVal(inData.cell)
        outData.description = Utility.cleanVal(inData.description)
        outData.cell_range = Utility.cleanVal(inData.cell_range)
        outData.inmate_classification_level = Utility.cleanVal(inData.inmate_classification_level)

        return outData;
    }

    getAllRecords = async () => {
        const query = "SELECT " +
            "cell_lookup_id as 'cell_lookup_id', " +
            "cell as 'cell', " +
            "description as 'description', " +
            "cell_range as 'cell_range', " +
            "inmate_classification_level as 'inmate_classification_level' " +
            "FROM `" + this.tablename + "` ";

        let dataMap = new Map();

        let conn
        let recordCnt = 0;
        try {
            conn = await sql.createConnection(this.dbConfig);
            const rows = await conn.query(query)
            // rows[0] contains the result set
            // loop over each row to transpose into individual record rows
            recordCnt = rows[0].length;
            Utility.Log(this.writerLog, " === === " + recordCnt + " rows fetched from cell_lookup table");
            for (const dbRow of rows[0]) {
                let row = this.eaCellLookupRow(dbRow);
                const key = Utility.toLowerCase(row["cell"]);
                if (Utility.isNotEmpty(key)) {
                    if (!dataMap.has(key)) {
                        dataMap.set(key, row.inmate_classification_level);
                    } else {
                        // Ignore duplicate rows
                    }
                }
            }
            return [recordCnt, dataMap];
        } catch (dbErr) {
            Utility.Log(this.writerLog,dbErr);
            throw dbErr;
        } finally {
            if (conn && conn.end) conn.end();
        }
    }
}
