'use strict'

import fs from 'fs';
import { parse } from 'csv-parse';
import { Utility }  from './Utility.js';

export class InmateDataIn {
    filename = "";
    columnNames= [
        "last_name",
        "first_name",
        "middle_name",
        "dob",
        "book_date",
        "cell_ref",
        "so_num",
        "gender",
        "race"
    ];

    constructor(filename) {
        this.filename = filename;
    }

    /*
     *  fieldHandling - add any field-specific value
     *   cleanup or special handling here
     */
    fieldHandling = (column, rawFieldValue) => {
        // The input file has no headers, so we will manually map the values by column
        let fieldName = this.columnNames[column];
        let fieldValue = Utility.cleanVal(rawFieldValue);

        return {fieldName, fieldValue};
    }

    readAndParse = async () => {
        let headers = [];
        let dataMap = new Map();

        const parser = fs
            .createReadStream(this.filename)
            .pipe(parse({
                // CSV options if any
            }));
        /*
         *  Loop through the file rows
         *  Create a map of the data
         */
        let recordCnt = 0;
        for await (const record of parser) {
            /* ***************************
             *  NOTE:
             *   Some of the older data files did not have all the required columns
             *   We will skip these files in our processing
             * ***************************
             */
            if (record.length !== 9) {
                break;
            }
            // Grab the headers from record 0
            if (recordCnt === -1) {   // Never happens - this file has no headers
                headers = [...record];
            } else {
                // Convert to object
                let recordObj = {};
                for (let i = 0; i < record.length; i++) {
                    let {fieldName,fieldValue} = this.fieldHandling(i, record[i]);
                    recordObj[fieldName] = fieldValue;
                }
                const key = recordObj["so_num"];
                if (Utility.isNotEmpty(key)) {
                    if (!dataMap.has(key)) {
                        dataMap.set(key, recordObj);
                    } else {
                        // Ignore duplicate rows
                    }
                }
            }
            recordCnt++;
        }

        return [recordCnt,dataMap];
    }
}
