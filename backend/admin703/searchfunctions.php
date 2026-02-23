<link rel="stylesheet" href="css/select2/select2.min.css"/>
<script src="js/plugins/select2.min.js"></script>
<?php
if (!isset($isRideShare) || empty($isRideShare)) {
    $isRideShare = 0;
} ?>
<script>
    $('body').on('keyup', '.select2-search__field', function () {
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").addClass("hideoptions");
        if ($(".select2-results__options").is(".select2-results__message")) {
            $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }
    });

    function formatDesign(item) {
        //console.log(item.text);
        /*if(item.text == 'Searching…'){
            console.log('item1');
           $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        }*/
        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        if (!item.id) {
            return item.text;
        }
        //console.log(item);
        var selectionText = item.text.split("--");
        if (selectionText[2] != null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + "</br>" + selectionText[2] + '</span>');
        } else if (selectionText[2] == null && selectionText[1] != null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[1] + '</span>');
        } else if (selectionText[2] != null && selectionText[1] == null) {
            var $returnString = $('<span>' + selectionText[0] + '</br>' + selectionText[2] + '</span>');
        }
        //$(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");
        return $returnString;
    }

    function formatDesignnew(item) {
        if (!item.id) {
            return item.text;
        }
        var selectionText = item.text.split("--");
        return selectionText[0];
    }

    $(function () {
        $("select.filter-by-text#searchKiosk").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_kiosk_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Kiosk',
                            searchDriverHotel: $('#searchDriverHotel').val(),
                            searchKioskHotel: $('#searchKioskHotel').val(),
                            trackingCompany: $('#trackingCompany').val(),
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;

                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");

                        return {
                            results: $.map(data, function (item) {

                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });

    $(function () {
        $("select.filter-by-text#searchDriver").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Driver',
                            company_id: $('#searchCompany option:selected').val(),
                            searchDriverHotel: $('#searchDriverHotel').val(),
                            searchRiderHotel: $('#searchRiderHotel').val(),
                            trackingCompany: $('#trackingCompany').val(),
                            usertype: function () {
                                if ($('#rideShare').val() == 1) {
                                    return 'Rider';
                                } else {
                                    return 'Driver';
                                }
                            },
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;

                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");

                        return {
                            results: $.map(data, function (item) {

                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });

    $(function () {
        $("select.filter-by-text#searchRider").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Rider',
                            searchDriverHotel: $('#searchDriverHotel').val(),
                            searchRiderHotel: $('#searchRiderHotel').val(),
                            trackingCompany: $('#trackingCompany').val(),
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;

                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");

                        return {
                            results: $.map(data, function (item) {

                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });

    $(function () {
        $("select.filter-by-text#searchCompany").each(function () {
            $(this).select2({
                allowClear: true,
                placeholder: $(this).attr('data-text'),
                // minimumInputLength: 2,
                templateResult: formatDesign,
                templateSelection: formatDesignnew,
                ajax: {
                    url: 'ajax_getdriver_detail_search.php',
                    dataType: "json",
                    type: "POST",
                    async: true,
                    delay: 250,
                    // quietMillis:100,
                    data: function (params) {
                        // console.log(params);
                        var queryParameters = {
                            term: params.term,
                            page: params.page || 1,
                            usertype: 'Company',
                        }
                        //console.log(queryParameters);
                        return queryParameters;
                    },
                    processResults: function (data, params) {
                        //console.log(data);
                        params.page = params.page || 1;

                        if (data.length < 10) {
                            var more = false;
                        } else {
                            var more = (params.page * 10) <= data[0].total_count;
                        }

                        $(".select2-container .select2-dropdown .select2-results .select2-results__options").removeClass("hideoptions");

                        return {
                            results: $.map(data, function (item) {

                                if (item.Phoneno != '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                                } else if (item.Phoneno == '' && item.vEmail != '') {
                                    var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                                } else if (item.Phoneno != '' && item.vEmail == '') {
                                    var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                                }
                                return {
                                    text: textdata,
                                    id: item.id
                                }
                            }),
                            pagination: {
                                more: more
                            }
                        };

                    },
                    transport: function(params, success, failure){
                        params.beforeSend = function(request){
                            mO4u1yc3dx(request);
                        };

                        var $request = $.ajax(params);
                        $request.then(success);
                        return $request;
                    },
                    cache: false
                }
            }); //theme: 'classic'
        });
    });
    // Fetch the preselected item, and add to the control
    var sId = '<?= !empty($searchDriver) ? $searchDriver : '';?>';
    var sSelect = $('select.filter-by-text#searchDriver');
    var sIdRider = '<?= !empty($searchRider) ? $searchRider : '';?>';
    var sSelectRider = $('select.filter-by-text#searchRider');
    var sIdCompany = '<?= !empty($searchCompany) ? $searchCompany : '';?>';
    var sSelectCompany = $('select.filter-by-text#searchCompany');
    var sIdKiosk = '<?= !empty($searchKiosk) ? $searchKiosk : '';?>';
    var sSelectKiosk = $('select.filter-by-text#searchKiosk');
    var itemname;
    var itemid;
    var isRideShare = '<?= $isRideShare; ?>';

    // console.log('isRideShare' + isRideShare);
    if (isRideShare == 1 && sId != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Rider',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'

        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelect.append(option).trigger('change');
            } else {
                // console.log(response.result);
            }
        });
    }
    if (sId != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sId + '&usertype=Driver&trackingCompany=' + $('#trackingCompany').val(),
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelect.append(option).trigger('change');
            } else {
                // console.log(response.result);
            }
        });
    }

    if (sIdKiosk != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdKiosk + '&usertype=Kiosk&trackingCompany=' + $('#trackingCompany').val(),
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'

        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectKiosk.append(option).trigger('change');
            } else {
                // console.log(response.result);
            }
        });
    }

    if (sIdRider != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdRider + '&usertype=Rider&trackingCompany=' + $('#trackingCompany').val(),
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'

        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectRider.append(option).trigger('change');
            } else {
                // console.log(response.result);
            }
        });
    }

    if (sIdCompany != '') {

        var ajaxData = {
            'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_getdriver_detail_search.php?id=' + sIdCompany + '&usertype=Company',
            'AJAX_DATA': "",
            'REQUEST_DATA_TYPE': 'json'
        };
        getDataFromAjaxCall(ajaxData, function (response) {
            if (response.action == "1") {
                var data = response.result;
                $.map(data, function (item) {
                    if (item.Phoneno != '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail + "--" + "Phone: +" + item.Phoneno;
                    } else if (item.Phoneno == '' && item.vEmail != '') {
                        var textdata = item.fullName + "--" + "Email: " + item.vEmail;
                    } else if (item.Phoneno != '' && item.vEmail == '') {
                        var textdata = item.fullName + "--" + "Phone: +" + item.Phoneno;
                    }
                    var textdata = item.fullName;
                    itemname = textdata;
                    itemid = item.id;
                });
                var option = new Option(itemname, itemid, true, true);
                sSelectCompany.append(option);
            } else {
                // console.log(response.result);
            }
        });
    }
    var $eventSelect = $("select.filter-by-text#searchCompany");
    $eventSelect.on("change", function (e) {
        $('select.filter-by-text#searchDriver').val(null).trigger('change');
        $('select.filter-by-text#searchRider').val(null).trigger('change');
        $('select.filter-by-text#searchKiosk').val(null).trigger('change');
    });
</script>