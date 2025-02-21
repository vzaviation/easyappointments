'use strict'

import fs from 'fs';
import { parse } from 'csv-parse';
import { Utility }  from './Utility.js';

export class ProcessedFile {
    filename = "";
    columnNames= [
        "file_name"
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
        let dataArr = [];

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
                const key = recordObj["file_name"];
                if (Utility.isNotEmpty(key)) {
                    if (!dataArr.includes(key)) {
                        dataArr.push(key)
                    }
                }
            }
            recordCnt++;
        }

        return [recordCnt,dataArr];
    }
}
