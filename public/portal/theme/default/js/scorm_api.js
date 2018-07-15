var getScormAPI = function (lmsContextData, initializationData) {
    const SUPPORTED_CMI_ELEMENTS = {
        /**
         * read-only data
         */
        CORE_CHILDREN : "cmi.core._children",

        /**
         * read-only data
         */
        STUDENT_ID : "cmi.core.student_id",

        /**
         * read-only data
         * format : lastname, firstname
         */
        STUDENT_NAME : "cmi.core.student_name",

        /**
         * Read-only element
         */
        CREDIT : "cmi.core.credit",

        /**
         * read-only element
         * Format : HH:MM:SS
         * Default value : "0000:00:00"
         */
        TOTAL_TIME : "cmi.core.total_time",

        /**
         * Read-only element
         */
        ENTRY : "cmi.core.entry",

        EXIT : "cmi.core.exit",
        LESSON_LOCATION : "cmi.core.lesson_location",
        LESSON_STATUS : "cmi.core.lesson_status",
        SCORE_CHILDREN : "cmi.core.score._children",
        SCORE_RAW : "cmi.core.score.raw",
        SESSION_TIME : "cmi.core.session_time",
        SUSPEND_DATA : "cmi.suspend_data",
        LAUNCH_DATA : "cmi.launch_data"
    };

    const DEFAULT_TOTAL_TIME_SPENT = "0000:00:00";

    const DEFAULT_SESSION_TIME = "0000:00:00";

    const DEFAULT_ENTRY_VALUE = "ab initio";

    const DEFAULT_LESSON_STATUS = "not attempted";

    var getCoreChildrenElements = function () {
        var elementsString = "";
        for (var element in SUPPORTED_CMI_ELEMENTS) {
            if (
                SUPPORTED_CMI_ELEMENTS.hasOwnProperty(element) &&
                (SUPPORTED_CMI_ELEMENTS[element] !== SUPPORTED_CMI_ELEMENTS.CORE_CHILDREN))
            {
                elementsString += SUPPORTED_CMI_ELEMENTS[element];
            }
        }

        return elementsString;
    };

    var getScoreRelatedElements = function () {
        return SUPPORTED_CMI_ELEMENTS.SCORE_RAW;
    };

    var scormRuntimeCache = {
        "cmi.core._children" : getCoreChildrenElements(),

        "cmi.core.student_id" : initializationData.scorm_runtime_activity_data.hasOwnProperty("user_id")?
            initializationData.user_id : null,
        "cmi.core.student_name" : initializationData.scorm_runtime_activity_data.hasOwnProperty("user_full_name")?
            initializationData.scorm_runtime_activity_data.user_full_name : null,

        "cmi.core.lesson_location" :
            initializationData.scorm_runtime_activity_data.hasOwnProperty("lesson_location")?
                initializationData.scorm_runtime_activity_data["lesson_location"] : "",
        "cmi.core.credit" : "credit",
        "cmi.core.lesson_status" :
            initializationData.scorm_runtime_activity_data.hasOwnProperty("lesson_status")?
                initializationData.scorm_runtime_activity_data["lesson_status"] : DEFAULT_LESSON_STATUS,
        "cmi.core.entry" : initializationData.scorm_runtime_activity_data.hasOwnProperty("entry")?
            initializationData.scorm_runtime_activity_data["entry"] : DEFAULT_ENTRY_VALUE,

        "cmi.core.total_time" : initializationData.scorm_runtime_activity_data.hasOwnProperty("total_time_spent")?
            secondsTohHHMMSS(initializationData.scorm_runtime_activity_data.total_time_spent) : DEFAULT_TOTAL_TIME_SPENT,
        "cmi.core.session_time" : DEFAULT_SESSION_TIME,

        "cmi.core.score._children" : getScoreRelatedElements(),
        "cmi.core.score.raw" : initializationData.scorm_runtime_activity_data.hasOwnProperty("score_raw")?
            initializationData.scorm_runtime_activity_data["score_raw"] : "",

        "cmi.suspend_data" : initializationData.scorm_runtime_activity_data.hasOwnProperty("suspend_data")?
            initializationData.scorm_runtime_activity_data["suspend_data"] : "",
        "cmi.launch_data" : ""
    };

    var makeScormAPIHttpRequests = function (requestObject) {
        if (jQuery !== undefined) {
            var request = $.ajax(requestObject);

            // Callback handler that will be called on success
            request.done(function (response, textStatus, jqXHR){
                console.log("Request successful");
            });

            // Callback handler that will be called on failure
            request.fail(function (jqXHR, textStatus, errorThrown){
                // Log the error to the console
                console.error(
                    "The following error occurred: "+
                    textStatus, errorThrown
                );
            });

            // Callback handler that will be called regardless
            // if the request failed or succeeded
            request.always(function () {
            });
        }
    };

    return {
        /**
         * @return {string}
         */
        LMSInitialize : function () {
            console.log("LMS is initialized");
            return "true";
        },

        /**
         * @return {string}
         */
        LMSGetValue : function (element) {
            if (scormRuntimeCache.hasOwnProperty(element)) {
                return scormRuntimeCache[element];
            }
        },

        /**
         * @return {string}
         */
        LMSSetValue : function (element, value) {
            console.log(scormRuntimeCache);
            console.log(element + " value is "+ scormRuntimeCache[element]);
            scormRuntimeCache[element] = value;
            console.log(element + " value is set to "+ scormRuntimeCache[element]);
            return "true";
        },

        /**
         * @return {string}
         */
        LMSCommit : function () {
            if (lmsContextData.hasOwnProperty("lmsUrl") && lmsContextData.hasOwnProperty("packet_id")
                && lmsContextData.hasOwnProperty("element_id")) {
                makeScormAPIHttpRequests(
                    {
                        url: lmsContextData.lmsUrl+"/program/update-scorm-runtime-activity",
                        type: "POST",
                        data: {
                            packet_id : lmsContextData.packet_id,
                            element_id : lmsContextData.element_id,
                            scorm_activity_data : {
                                session_time : HHMMSSToSeconds(scormRuntimeCache["cmi.core.session_time"]),
                                lesson_location : scormRuntimeCache["cmi.core.lesson_location"],
                                lesson_status : scormRuntimeCache["cmi.core.lesson_status"],
                                score_raw : scormRuntimeCache["cmi.core.score.raw"],
                                suspend_data : scormRuntimeCache["cmi.suspend_data"],
                                exit : scormRuntimeCache["cmi.core.exit"]
                            }
                        }
                    }
                );

                scormRuntimeCache["cmi.core.session_time"] = "0000:00:00";
            }

            return "true";
        },

        /**
         * @return {number}
         */
        LMSGetLastError : function () {
            return 0;
        },

        /**
         * @return {string}
         */
        LMSGetErrorString : function (errorCode) {
            return "Error string";
        },

        /**
         * @return {string}
         */
        LMSGetDiagnostic : function (errorCode) {
            return "Diagnostic string";
        },

        /**
         * @return {string}
         */
        LMSFinish : function () {
            this.LMSCommit();
        }
    };
};
