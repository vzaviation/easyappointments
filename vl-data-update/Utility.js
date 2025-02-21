'use strict'

import moment from 'moment';

// TODO: Convert date and time functions to use date-fns library instead of moment and regex

/**
 * Various utility functions for the scripts
 */
export class Utility {

    static addOneDay(inDate) {
        const theDate = moment(inDate);
        let newDate = theDate.add(1, "days");
        return newDate.format('YYYY-MM-DD');
    }

    static availNonZero(obj1) {
        let nonZero = false;

        if (
            ((Utility.isNotEmpty(obj1.infant_capacity)) &&
                (obj1.infant_capacity != "0")) ||
            ((Utility.isNotEmpty(obj1.toddler_capacity)) &&
                (obj1.toddler_capacity != "0")) ||
            ((Utility.isNotEmpty(obj1.pre_k_capacity)) &&
                (obj1.pre_k_capacity != "0")) ||
            ((Utility.isNotEmpty(obj1.school_aged_capacity)) &&
                (obj1.school_aged_capacity != "0"))
        ) {
            nonZero = true;
        }

        return nonZero;
    }

    static cleanLWDB(inVal) {
        const cleanupList = {
            "Greater Dallas" : "Dallas",
            "North Central Texas" : "North Central",
            "Northeast Texas" : "North East Texas",
            "Rural Capital Area" : "Rural Capital",
            "Tarrant County" : "Tarrant"
        };
        let outVal = inVal;
        if (this.isNotEmpty(inVal)) {
            if (this.isNotEmpty(cleanupList[inVal])) {
                outVal = cleanupList[inVal];
            }
        }

        return outVal;
    }

    static cleanOpNum(inVal) {
        const opNumRegex = new RegExp(/([\d]{1,})-([\d]{1,})[-]{0,1}.*/);
        if (this.isNotEmpty(inVal)) {
            let matchArr = inVal.match(opNumRegex);
            if (matchArr != null) {
                return matchArr[2];
            } else {
                return null;
            }
        }
    }

    static cleanVal(inVal) {
        if (this.isNotEmpty(inVal)) {
            try {
                return inVal.trim();
            } catch (e) {
                // If object def is other than string
                return inVal;
            }
        } else {
            return "";
        }
    }

    static curday(dayOffset = 0) {
        const curdate = moment();
        let newDate = curdate.add(dayOffset, "days");
        return newDate.format('YYYY-MM-DD');
    }

    static curtime() {
        return moment(new Date()).format('YYYY-MM-DD HH:mm:ss CT');
    }

    static DTBDate(inDate) {
        const yyyymmddRegex = new RegExp(/([\d]{4})-([\d]{2})-([\d]{2})/);

        let outDate = inDate;

        let matches = inDate.match(yyyymmddRegex);
        if (matches != null) {
            if (matches.length == 4) {
                outDate = matches[2] + "/" + matches[3] + "/" + matches[1];
            }
        }
        return outDate;
    }

    static DTBDateTime(inDate) {
        // Should be mm-dd-yyyy HH:MM:SS (24hr time - technically mm-dd-yyyy H:i:s)
        const yyyymmddTimeRegex = new RegExp(/^([\d]{4})-([\d]{2})-([\d]{2})\s([\d]{2}):([\d]{2}):([\d]{2})$/);

        let outDate = inDate;

        try {
            let matches = inDate.match(yyyymmddTimeRegex);
            if (matches != null) {
                outDate = String(matches[2]).padStart(2, '0') + "-" + String(matches[3]).padStart(2, '0') + "-" + matches[1];
                const hour = String(matches[4]).padStart(2, '0');
                const min = String(matches[5]).padStart(2, '0');
                const sec = String(matches[6]).padStart(2, '0');
                outDate = outDate + " " + hour + ":" + min + ":" + sec;
            }
        } catch (err) {
            // Ignore, silently
        } finally {
            return outDate;
        }
    }

    static enrollNonZero(obj1) {
        let nonZero = false;

        if (
            ((Utility.isNotEmpty(obj1.infant_enrollment)) &&
                (obj1.infant_enrollment != "0")) ||
            ((Utility.isNotEmpty(obj1.toddler_enrollment)) &&
                (obj1.toddler_enrollment != "0")) ||
            ((Utility.isNotEmpty(obj1.preschool_enrollment)) &&
                (obj1.preschool_enrollment != "0")) ||
            ((Utility.isNotEmpty(obj1.schoolage_enrollment)) &&
                (obj1.schoolage_enrollment != "0"))
        ) {
            nonZero = true;
        }

        return nonZero;
    }

    static fixGSDateFormat(inDate) {
        const dmyRegex = new RegExp(/([\d]{1,2})\/([\d]{1,2})\/(\d\d)/);

        let outDate = inDate;

        let matches = inDate.match(dmyRegex);
        if (matches != null) {
            if (matches.length == 4) {
                // 1 = month, 2 = day, 3 = 2-digit year -- format as YYYY-MM-DD
                outDate = "20" + matches[3] + "-" + String(matches[1]).padStart(2, '0') + "-" + String(matches[2]).padStart(2, '0');
                if (outDate == "2000-01-00") outDate = "";
            }
        }
        return outDate;
    }

    static isChangedCapEnr(obj1, obj2) {
        let isChanged = true;
        //  Skip the comparison if either or both is empty
        if (!this.isNotEmpty(obj1) || (Object.keys(obj1).length === 0) ||
            (!this.isNotEmpty(obj2)) || (Object.keys(obj2).length === 0)) {
            isChanged = false;
        } else {
            if ((obj1.infant_capacity == obj2.infant_capacity) &&
                (obj1.toddler_capacity == obj2.toddler_capacity) &&
                (obj1.pre_k_capacity == obj2.pre_k_capacity) &&
                (obj1.school_aged_capacity == obj2.school_aged_capacity) &&
                (obj1.reporting_status == obj2.reporting_status) &&
                (obj1.infant_enrollment == obj2.infant_enrollment) &&
                (obj1.toddler_enrollment == obj2.toddler_enrollment) &&
                (obj1.preschool_enrollment == obj2.preschool_enrollment) &&
                (obj1.schoolage_enrollment == obj2.schoolage_enrollment) &&
                (obj1.enrollment_status == obj2.enrollment_status)) {
                isChanged = false;
            }
        }
        return isChanged;
    }

    static isNotEmpty(value) {
        if ((value != undefined) &&
            (value !== "") &&
            (value !== "NULL")) {
            return true;
        } else {
            return false;
        }
    }

    static Log(writerObj, text) {
        //console.log(text);   -- Default to no console
        writerObj.write(text + "\n");
    }

    static makeInt(value) {
        let valueOut = value;
        if (this.isNotEmpty(value)) {
            valueOut = parseInt(value, 10);
            if (valueOut == NaN) valueOut = value;
        } else {
            valueOut = "";
        }
        return valueOut;
    }

    static objectsEqual(obj1, obj2) {
        let ret = true;

        const keys1 = Object.keys(obj1);
        const keys2 = Object.keys(obj2);

        if (keys1.length !== keys2.length) {
            ret = false;
        }

        // Are the keys the same?
        const keys1Arr = Array.from(keys1);
        for (let key of keys2) {
            if (!keys1Arr.includes(key)) {
                ret = false;
                break;
            }
        }

        // Finally are the values the same?
        for (let key of keys1) {
            if (obj1[key] !== obj2[key]) {
                ret = false;
                break;
            }
        }

        return ret;
    }

    static standardizeDateFormat(inDate, keepTime = false) {
        const dmyRegex = new RegExp(/^([\d]{1,2})\/([\d]{1,2})\/([\d\d]{1,}).*$/);
        const dmyTimeRegex = new RegExp(/^([\d]{1,2})\/([\d]{1,2})\/([\d\d]{1,})\s{1,}([\d]{1,2}:[\d]{1,2}[:\d\d\s\w\w]{0,})$/);
        const dmyDashRegex = new RegExp(/^([\d]{1,2})\-([\d]{1,2})\-([\d\d]{1,}).*$/);
        const dmyDashTimeRegex = new RegExp(/^([\d]{1,2})\-([\d]{1,2})\-([\d\d]{1,})\s{1,}([\d]{1,2}:[\d]{1,2}[:\d\d\s\w\w]{0,})$/);
        const YYMMDDRegex = new RegExp(/^([\d]{4})-([\d]{1,2})-([\d]{1,2}).*$/);
        const YYMMDDTimeRegex = new RegExp(/^([\d]{4})-([\d]{1,2})-([\d]{1,2})\s{1,}([\d]{1,2}:[\d]{1,2}[:\d\d\s\w\w]{0,})$/);

            try {
                if (this.isNotEmpty(inDate)) {
                    let outDate = inDate;

                    let matches = inDate.match(dmyRegex);
                    if (keepTime) {
                        matches = inDate.match(dmyTimeRegex);
                    }
                    if (matches == null) {
                        let matches = inDate.match(dmyDashRegex);
                        if (keepTime) {
                            matches = inDate.match(dmyDashTimeRegex);
                        }
                        if (matches == null) {
                            let matches = inDate.match(YYMMDDRegex);
                            if (keepTime) {
                                matches = inDate.match(YYMMDDTimeRegex);
                            }
                            if (matches != null) {
                                outDate = matches[1] + "-" + String(matches[2]).padStart(2, '0') + "-" + String(matches[3]).padStart(2, '0');
                                if (keepTime) {
                                    outDate = outDate + " " + this.standardizeTimeFormat(matches[4]);
                                }
                            }
                        } else {
                            let year4 = matches[3];
                            if (matches[3].length == 2) year4 = "20" + matches[3];
                            outDate = year4 + "-" + String(matches[1]).padStart(2, '0') + "-" + String(matches[2]).padStart(2, '0');
                            if (keepTime) {
                                outDate = outDate + " " + this.standardizeTimeFormat(matches[4]);
                            }
                        }
                    } else {
                        let year4 = matches[3];
                        if (matches[3].length == 2) year4 = "20" + matches[3];
                        outDate = year4 + "-" + String(matches[1]).padStart(2, '0') + "-" + String(matches[2]).padStart(2, '0');
                        if (keepTime) {
                            outDate = outDate + " " + this.standardizeTimeFormat(matches[4]);
                        }
                    }
                    if (outDate.substring(0, 10) == "2000-01-00") outDate = "";
                    return outDate;
                } else {
                    return inDate;
                }
            } catch (err) {
                console.log("*** *** ERR: " + err);
            }
    }

    static standardizeTimeFormat(inTime) {
        const timeRegex = new RegExp(/^([\d]{1,2}):([\d]{1,2})([\:\d\d]{0,})([\s\w\w]{0,})$/);
        let outTime = inTime;

        try {
            if (this.isNotEmpty(inTime)) {
                let matches = inTime.match(timeRegex);
                if (matches != null) {
                    if ((matches[3] == "") &&
                        (matches[4] == "")) {
                        // Time is hh:mm (assume 24h)
                        outTime = String(matches[1]).padStart(2, '0') + ":" + String(matches[2]).padStart(2, '0') + ":00";
                    } else if (matches[4] == "") {
                        // Time is hh:mm:ss (assume 24h)
                        outTime = String(matches[1]).padStart(2, '0') +
                            ":" + String(matches[2]).padStart(2, '0') +
                            ":" + String(matches[3]).padStart(2, '0');
                    } else {
                        // Convert to 24h hh:mm:ss
                        if (matches[4].trim() == "PM") {
                            matches[1] = parseInt(matches[1]) + 12;
                        }
                        outTime = String(matches[1]).padStart(2, '0') +
                            ":" + String(matches[2]).padStart(2, '0') +
                            ":" + String(matches[3]).padStart(2, '0');
                    }
                }
            }
        } catch (err) {
            // Do nothing, silently
        } finally {
            return outTime;
        }
    }

    static toLowerCase(inVal) {
        if (this.isNotEmpty(inVal)) {
            try {
                return inVal.toLowerCase();
            } catch (e) {
                // If object def is other than string
                return inVal;
            }
        } else {
            return "";
        }
    }
}
