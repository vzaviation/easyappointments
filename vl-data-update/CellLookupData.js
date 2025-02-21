'use strict'

import fs from 'fs';
import { parse } from 'csv-parse';
import { Utility } from './Utility.js';

export class CellLookupData {
    filename = "";

    constructor(filename) {
        this.filename = filename;
    }

    /*
     *  fieldHandling - add any field-specific value
     *   cleanup or special handling here
     */
    fieldHandling = (fieldName, rawFieldValue) => {
        let fieldValue = rawFieldValue;
        if (fieldName === "full_cell") {
            fieldValue = Utility.toLowerCase(Utility.cleanVal(fieldValue));
        } else {
            fieldValue = Utility.cleanVal(fieldValue);
        }

        return fieldValue;
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
            // Grab the headers from record 0
            if (recordCnt === 0) {
                for (let i = 0; i < record.length; i++) {
                    headers[i] = record[i].trim().toLowerCase();
                }
            } else {
                // Convert to object
                let recordObj = {};
                for (let i = 0; i < headers.length; i++) {
                    recordObj[headers[i]] = this.fieldHandling(headers[i], record[i]);
                }
                const key = recordObj["full_cell"];
                if (Utility.isNotEmpty(key)) {
                    if (!dataMap.has(key)) {
                        dataMap.set(key, recordObj.inmate_classification_level)
                    }
                }
            }
            recordCnt++;
        }

        return [recordCnt,dataMap];
    }

}
