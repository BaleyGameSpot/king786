/**
 *-----------------------------------------------------------------
 * Additional validation patterns
 *-----------------------------------------------------------------
 **/
$(function () {
     //alert(_system_script);
    $.validator.addMethod("validate_facebook_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)facebook\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_twitter_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)twitter\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_googleplus_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)plus.google\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_linkedin_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)linkedin\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_youtube_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)youtube\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("validate_pinterest_url", function (value, element) {
        return this.optional(element) || /^(https?:\/\/)?((w{3}\.)?)pinterest\.com\/(#!\/)?[a-z0-9_/+&%.?=]+$/i.test(value);
    });
    $.validator.addMethod("phonevalidate", function (value, element) {
        var value = value.split(" ").join("");
        value = value.replace(/\(|\)|\s+|-/g, '');
        return this.optional(element) || /^(?:[0-9] ?){6,14}[0-9]$/.test(value);
    });
    $.validator.addMethod("validate_prefix_code", function (value, element) {
        return this.optional(element) || /^\+(([2-9]{1}([0-9]{0,2})$)|([1]{1}?(\s)?([1-9]{1}[0-9]{2})$)|([1-9]{1}$))/i.test(value);
    });
    $.validator.addMethod("validate_name", function (value, element) {
        return this.optional(element) || /^[a-zA-Z\s\(\)\_\-\"\'\,\:\`\\\/\.\{\}\[\]]+$/i.test(value);
    });
    $.validator.addMethod("validate_date", function (value, element) {
        return this.optional(element) || /^\d\d?-\d\d-\d\d\d\d/.test(value);
    });
    $.validator.addMethod("validate_zipcode", function (value, element) {
        var value = value.split(" ").join("");
        return this.optional(element) || /^[0-9a-zA-Z\s{0,1}]{5,6}$/.test(value);
    });
    $.validator.addMethod('maxStrict', function (value, el, param) {
        return value <= param;
    });

    $.validator.addMethod("greaterThan",
        function (value, element, param) {
            var $min = $(param);
            if (this.settings.onfocusout) {
                $min.off(".validate-greaterThan").on("blur.validate-greaterThan", function () {
                    $(element).valid();
                });
            }
            if (param != '') {
                return parseInt(value) > parseInt($min.val());
            } else {
                return true;
            }
        }, "Max must be greater than min");

    /*    $.validator.addMethod("noSpace", function(value, element) { 
     return value.indexOf("") < 0 && value != ""; 
     }, "Password should not contain whitespace.");*/

    $.validator.addMethod("noSpace", function (value, element) {
        return this.optional(element) || /^\S+$/i.test(value);
    }, "Password should not contain whitespace.");

    $.validator.addMethod("OnlySpaceNotAllowed", function (value, element) {
        return this.optional(element) || /.*\S.*/i.test(value);
    }, "This field is required.");

    /*jQuery.validator.addMethod("noSpaceTitle", function(value, element) { 
        return value.indexOf(" ") < 0 && value != ""; 
    }, "No space please and don't leave it empty");*/

    jQuery.validator.addMethod("alphanumericspace", function (value, element) {
        return this.optional(element) || /^[a-z\d\-_\s]+$/i.test(value);
    }, "Invalid Attempt, only alphanumeric, dash, underscore and space are allowed.");

    jQuery.validator.addMethod("dateGreaterThan", function (value, element) {
        var startDate = $('#dStartDate').val();
        return Date.parse(startDate) <= Date.parse(value) || value == "";
    }, "* Expiry Date must be greater than Activation Date");

    $.validator.addMethod("notZero", function (value, element, param) {
        return this.optional(element) || parseInt(value) > 0;
    });
    
    jQuery.validator.addMethod("allowZeroForGroupValidation", function(value, element, params) {
        var allowZero = false;
        $('.reward_setting_'+params).each(function() {
            if ($(this).val() != '' && $(this).val() != "0" && $(this).val() > "0") {
                allowZero = true;
            }
        });
        return allowZero;
    }, "Please enter a value greater than 0, as at least one field requires a value greater than 0.");
   
    $.validator.addMethod("wysihtml5Required", function (value, element,params) {
        var editorObj1 = $('#'+params.id_of_field+params.Default_Lang).data('wysihtml5');
        var editorElem1 = editorObj1.editor;
        var editorContent = editorElem1.getValue().replace(/&nbsp;/g, '').trim().length;
        
        console.log(editorContent);
        console.log(editorElem1.getValue().trim());
        console.log(editorElem1.getValue().replace(/&nbsp;/g, ''));
        return editorContent !== 0;
        
    }, "This field is required.");
    
    
});

$(function () {

    //Admin Start
    if (_system_script == 'Admin' || _system_script == 'Hotels') {
        var errormessage;
        
        if ($('#_admin_form').length !== 0) {
            $('#_admin_form').validate({
                ignore: 'input[type=hidden],:hidden',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImgName: {
                        extension:imageUploadingExtenstionjsrule
                    },
                    vVehicleTypeImg: {
                        extension:imageUploadingExtenstionjsrule
                    },
                    vFirstName: {required: true, minlength: 1, maxlength: 30},
                    vLastName: {minlength: 1, maxlength: 30},
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    fHotelServiceCharge: {number: true},
                    vContactNo: {
                        required: true,
                        minlength: 3,
                        digits: true, // phonevalidate: true
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone_admin.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }, vCountry: function () {
                                    return $("#vCountry").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted' && iGroupId == '4') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    alert(errormessage);
                                    return false;
                                } else if (response == 'false' && iGroupId == '4') {
                                    errormessage = "Phone number is already exist.";
                                    alert(errormessage);
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    //vPhone: {required: true, phonevalidate: true},
                    iGroupId: {required: true}
                },
                messages: {
                    vImgName: {
                        extension: imageUploadingExtenstionMsg
                    },
                    vVehicleTypeImg: {
                        extension: imageUploadingExtenstionMsg
                    },
                    vFirstName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    /*vPhone: {
                     required: 'This field is required.',
                     phonevalidate: 'Please enter valid Phone Number.'
                     },*/
                    vContactNo: {
                        remote: function () {
                            return errormessage;
                        },
                        phonevalidate: 'Please enter valid Phone Number.'
                    },
                    iGroupId: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled', false);
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Admin End

    // profile start
    if (_system_script == 'profile') {
        var errormessage;
        if ($('#_admin_form').length !== 0) {
            $('#_admin_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vImgName: {extension:imageUploadingExtenstionjsrule},
                    vVehicleTypeImg: {extension:imageUploadingExtenstionjsrule},
                    vFirstName: {required: true, minlength: 1, maxlength: 30},
                    vLastName: {required: true, minlength: 1, maxlength: 30},
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iAdminId: function () {
                                    return $("#iAdminId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    fHotelServiceCharge: {number: true},
                    vContactNo: {required: true, minlength: 3, digits: true}, // phonevalidate: true
                    iGroupId: {required: true}
                },
                messages: {
                    vImgName: {
                        extension: imageUploadingExtenstionMsg
                    },
                    vVehicleTypeImg: {
                        extension: imageUploadingExtenstionMsg
                    },
                    vFirstName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vContactNo: {
                        required: 'This field is required.',
                        phonevalidate: 'Please enter valid Phone Number.'
                    },
                    iGroupId: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled', false);
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    // profile end
    // map api service action form validation
    if ($('#_map_api_setting_action_form').length !== 0) {
        $('#_map_api_setting_action_form').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
                vUsageOrder: {
                    required: true,
                    remote: {
                        url: _system_admin_url + 'ajax_validate_usage_order.php',
                        type: "post",
                        data: {
                            usageOrder: function () {
                                return $("#vUsageOrder").val();
                            }, sid: function () {
                                return $("#sid").val();
                            }, id: function () {
                                return $("#id").val();
                            }, map_api_setting: function () {
                                return "Yes";
                            }
                        },
                        beforeSend : function(xhr) {
                            mO4u1yc3dx(xhr);
                        },
                        dataFilter: function (response) {
                            if (response > 0) {
                                errormessage = "Usage order is assigned, please select different.";
                                return false;
                            } else {
                                return true;
                            }
                        },
                        async: false
                    }
                },
                eStatus: {required: true}
            },
            messages: {
                vUsageOrder: {
                    required: 'This field is required.',
                    remote: 'Usage order is assigned, please select different.'
                },
                eStatus: {
                    required: 'This field is required.'
                }
            },
            submitHandler: function (form) {
                if ($(form).valid()) {
                    ShpSq6fAm7(form);
                    form.submit();
                }
                return false; // prevent normal form posting
            }
        });
    }
    // Map API Mongo Auth Places start
    // Map API Mongo Auth Places start
    if ($('#_authmongoplaces_form').length !== 0) {
        $('#_authmongoplaces_form').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
                vTitle: {required: true},
                // vServiceId: {required: true},
                vAuthKey: {
                    required: true,
                    noSpace: true,
                    // remote: {
                    // url: _system_admin_url + 'ajax_validate_auth_key.php',
                    // type: "post",
                    // data: {
                    // vAuthKey: function () {
                    // return $("#vAuthKey").val();
                    // }, vServiceAccountId: function () {
                    // return $("#vServiceAccountId").val();
                    // }, id: function () {
                    // return $("#id").val();
                    // }, search_address: function () {
                    // return $("#search_address").val();
                    // }
                    // },
                    // dataFilter: function (response) {    
                    // if (response == false) {
                    // return false;
                    // } else {
                    // return true;
                    // }
                    // },
                    // async: false
                    // }
                },
                vUsageOrder: {
                    required: true,

                    remote: {
                        url: _system_admin_url + 'ajax_validate_usage_order.php',
                        type: "post",
                        async: false,
                        data: {
                            usageOrder: function () {
                                return $("#vUsageOrder").val();
                            }, sid: function () {
                                return $("#sid").val();
                            }, id: function () {
                                return $("#id").val();
                            }
                        },
                        beforeSend : function(xhr) {
                            mO4u1yc3dx(xhr);
                        },
                        dataFilter: function (response) {
                            if (response > 0) {
                                errormessage = "Usage order is assigned, please select different.";
                                return false;
                            } else {
                                return true;
                            }
                        }

                    }
                },
                eStatus: {required: true}
            },
            messages: {
                vTitle: {
                    required: 'This field is required.'
                },
                vServiceId: {
                    required: 'This field is required.'
                },
                vAuthKey: {
                    required: 'This field is required.',
                    noSpace: 'Auth key should not contain whitespace.',
                    remote: 'auth key is invalid.'
                },
                vUsageOrder: {
                    required: 'This field is required.',
                    remote: 'Usage order is assigned, please select different.'
                },
                eStatus: {
                    required: 'This field is required.'
                }
            },
            submitHandler: function (form) {
                var resultAuth = 0;
                if ($(form).valid()) {
                    $.ajax({
                        url: _system_admin_url + 'ajax_validate_auth_key.php',
                        type: 'post',
                        async: false,
                        data: {
                            vAuthKey: function () {
                                return $("#vAuthKey").val();
                            }, vServiceAccountId: function () {
                                return $("#vServiceAccountId").val();
                            }, id: function () {
                                return $("#id").val();
                            }, search_address: function () {
                                return $("#search_address").val();
                            }
                        },
                        dataFilter: function (response) {
                            if (response == 1) {
                                resultAuth = 1;

                            }
                        }
                    });
                    if (resultAuth == 1) {
                        $('#vAuthKey-error').text('')
                        // hideLoader();
                        // this.submit();
                        // return true;
                        form.submit();
                    } else {
                        console.log("else");
                        $('#vAuthKey-error').text('auth key is invalid.')
                        // return false;
                    }
                }
                return false; // prevent normal form posting
            }
        });
    }

    //vehicles Start
    if (_system_script == 'Vehicle') {
        if ($('#_vehicle_form').length !== 0) {
            $('#_vehicle_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
    
                    if(e.attr("name") == "iModelId")
                    {
                        $("#carmdl").after(error);
                    }else {
                        e.parents('.row > div').append(error);
                    }
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iMakeId: {required: true},
                    iModelId: {required: true},
                    iYear: {required: true},
                    vLicencePlate: {required: true},
                    //iCompanyId: { required: true },
                    iDriverId: {required: true},
                    'vCarType[]': {required: true}
                },
                messages: {
                    iMakeId: {
                        required: 'This field is required.'
                    },
                    iModelId: {
                        required: 'This field is required.'
                    },
                    iYear: {
                        required: 'This field is required.'
                    },
                    vLicencePlate: {
                        required: 'This field is required.'
                    },
                    /*iCompanyId: {
                        required: 'This field is required.'
                    },*/
                    iDriverId: {
                        required: 'This field is required.'
                    },
                    'vCarType[]': {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //vehiclesp End

    //Coupon Start
    if (_system_script == 'Coupon') {
        if ($('#_coupon_form').length !== 0) {
            var tDescription_lang = 'tDescription_' + site_default_lang;
            $('#_coupon_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCouponCode: {required: true},
                    tDescription: {
                        required: {
                            depends: function (element) {
                                return $('#tDescription_Default').length !== 0;
                            }
                        },
                        minlength: 2
                    },
                    tDescription_lang: {
                        required: {
                            depends: function (element) {
                                return $('#tDescription_Default').length === 0;
                            }
                        },
                        minlength: 2
                    },
                    fDiscount: {
                        required: {
                            depends: function (element) {
                                if ($('[name="eFreeDelivery"]').length > 0) {
                                    if ($('[name="eFreeDelivery"]').is(':checked') === false) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                }
                                return true;
                            }
                        },
                        number: true, maxStrict: function () {
                            if ($("#eType").val() == "percentage") {
                                return 100;
                            } else {
                                return 3000;
                            }
                        }
                    },
                    iUsageLimit: {required: true, number: true},
                    dActiveDate: {
                        required: function () {
                            return $("input[name='eValidityType']:checked").val() == "Defined";
                        }
                    },
                    dExpiryDate: {
                        required: function () {
                            return $("input[name='eValidityType']:checked").val() == "Defined";
                        }
                    },
                    vPromocodeType: {required: true},
                    iServiceId: {
                        required: {
                            depends: function (element) {
                                if ($('[name="eStoreType"]:checked').val() == "StoreSpecific") {
                                    return true;
                                }
                                return false;
                            }
                        }
                    },
                    iCompanyId: {
                        required: {
                            depends: function (element) {
                                if ($('[name="eStoreType"]:checked').val() == "StoreSpecific") {
                                    return true;
                                }
                                return false;
                            }
                        }
                    }
                },
                messages: {
                    vCouponCode: {
                        required: 'This field is required.',
                    },
                    tDescription: {
                        required: 'This field is required.',
                        minlength: 'Description at least 2 characters long.'
                    },
                    fDiscount: {
                        required: 'This field is required.',
                        maxStrict: function () {
                            if ($("#eType").val() == "percentage") {
                                return 'Please enter between 1 to 100 only.';
                            } else {
                                return 'Please enter between 1 to 3000 only.';
                            }
                        }
                    },
                    iUsageLimit: {
                        required: 'This field is required.'
                    },
                    dActiveDate: {
                        required: 'This field is required.'
                    },
                    dExpiryDate: {
                        required: 'This field is required.'
                    },
                    vPromocodeType: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Coupon End

    //DeliverAllStore Start
    if (_system_script == 'DeliverAllStore') {
        var errormessage;
        if ($('#_company_form').length !== 0) {
            // console.log("111111");
            $('#_company_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                /*errorPlacement: function (error, e) {
                 e.parents('.row > div').append(error);
                 },*/
                errorPlacement: function (error, element) {
                    if (element.attr("name") == "cuisineId[]" ) {
                        error.insertAfter(".CuisineClass");
                    }else if(element.attr("name") == "iServiceId[]")
                    {
                        error.insertAfter(".ServiceTypeClass");
                    }else if (element.attr("name") == "vFromMonFriTimeSlot1") {
                        error.appendTo(".FromError1");
                    } else if (element.attr("name") == "vToMonFriTimeSlot1") {
                        error.appendTo(".ToError1");
                    } else if (element.attr("name") == "vFromSatSunTimeSlot1") {
                        error.appendTo(".FromError2");
                    } else if (element.attr("name") == "vToSatSunTimeSlot1") {
                        error.appendTo(".ToError2");
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCompany: {required: true, minlength: 1, maxlength: 100},
                    'iServiceId[]': {required: true},
                    
                    iMaxItemQty: {required: true, digits: true, min: 0},
                    fPrepareTime: {required: true, digits: true, min: 0},
                    fOfferAppyType: {required: true},
                    fPricePerPerson: {required: true, number: true, /*digits: true,*/ min: 1},
                    vEmail: {
                        /* required: true,*/ email: true,
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vCaddress: {required: true, minlength: 2},
                    vZip: {required: true, minlength: 2, alphanumeric: true},
                    vLang: {required: true},
                    vContactName: {required: true},
                    'cuisineId[]': {required: true},
                    fMinOrderValue: {number: true},
                    fPackingCharge: {number: true},
                    vCountry: {required: true},
                    fOfferAmt: {
                        number: function () {
                            return $("#fOfferAmt").prop('required');
                        },
                        min: function () {
                            return $("#fOfferAmt").prop('required');
                            //    return $("#fOfferAmtDiv").is(":visible");
                        },
                        max: function () {
                            if ($("#fOfferAmt").prop('required') == true && $("#fOfferType").val() == 'Percentage') {
                                return 100;
                            }
                        }
                    },
                    fTargetAmt: {
                        number: function () {
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return true;
                            }
                        },
                        greaterThan: function () {
                            if ($("#fTargetAmt").prop('required') == true && $("#fOfferType").val() != 'Percentage') {
                                return '#fOfferAmt';
                            } else {
                                return '';
                            }
                        },
                        min: function () {
                            return $("#fTargetAmt").prop('required');
                        }
                    },
                    fMaxOfferAmt: {number: true},
                    vRestuarantLocation: {required: true},
                    vImage:{
                        // required: true,
                        extension:imageUploadingExtenstionjsrule
                    },
                    vKioskImage:{
                        extension:imageUploadingExtenstionjsrule
                    }
                },
                messages: {
                    vCompany: {
                        minlength: 'Name at least 2 characters long.',
                        maxlength: 'Please enter less than 100 characters.'
                    },
                    iMaxItemQty: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only',
                        min: 'Please Enter Value Greater than Zero.',
                    },
                    vEmail: {
                        //required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    vContactName: {
                        required: 'This field is required.'
                    },
                    'cuisineId[]': {
                        required: 'This field is required.'
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    fOfferAmt: {
                        min: 'Please Enter Value Greater than Zero.'
                    },
                    fTargetAmt: {
                        greaterThan: 'Target Amount must be greater than Offer amount for Flat offer type.',
                        min: 'Please Enter Value Greater than Zero.'
                    },
                    vRestuarantLocation: {
                        required: 'This field is required.'
                    },
                    fPrepareTime: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only.',
                        min: 'Please Enter Value Greater than Zero.',
                    },
                    fPricePerPerson: {
                        required: 'This field is required.',
                        digits: 'Please enter numeric value only.',
                        min: 'Please Enter Value Greater than Zero.',
                    },
                    vImage: {
                        required: requiredFieldMsg,
                        extension: imageUploadingExtenstionMsg
                    },
                    vKioskImage: {
                        extension: imageUploadingExtenstionMsg
                    }


                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled', false);
                    $("#vLang").prop('disabled', false);
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //DeliverAllStore End


    //Food Menu Item Validation  Start
    if (_system_script == 'MenuItems') {
        var errormessage;
        if ($('#menuItem_form').length !== 0) {
            $('#menuItem_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block error',
                errorElement: 'span',
                /* errorPlacement: function(error, element) {
                 if (element.attr("name") == "cuisineId[]")
                 {
                 error.insertAfter(".CuisineClass");
                 } else {
                 error.insertAfter(element);
                 }
                 },*/
                onkeyup: function (element) {
                    $(element).valid()
                },
                highlight: function (e) {
                    if ($(e).attr("name") == "OptPrice[]" || $(e).attr("name") == "AddonOptions[]" || $(e).attr("name") == "BaseOptions[]" || $(e).attr("name") == "AddonPrice[]") {
                        $(e).closest('.row .form-group').removeClass('has-success has-error').addClass('has-error');
                    } else {
                        $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    }
                    //$(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row .form-group').removeClass('has-success has-error');
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iCompanyId: {required: true},
                    iFoodMenuId: {required: true},
                    fPrice: {required: true, number: true},
                    fOfferAmt: {number: true},
                    'BaseOptions[]': {required: true},
                    'OptPrice[]': {required: true, number: true},
                    'AddonOptions[]': {required: true},
                    'AddonPrice[]': {required: true, number: true},
                    vItemType_Default: {required: true},
                    vSKU: {
                        required: function (element) {
                            $(element).val($(element).val().trim());
                            if ($(element).attr('required')) {
                                return true;
                            }
                            return false;
                        },
                        alphanumericspace: true,
                        remote: {
                            url: _system_admin_url + 'ajax_check_item_sku.php',
                            type: "post",
                            data: {
                                iMenuItemId: function () {
                                    return $("#iMenuItemIdedit").val();
                                },
                                iFoodMenuId: function () {
                                    return $("#iFoodMenuId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                if (response == 'false') {
                                    errormessage = "SKU already exists.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                        }
                    }
                },
                messages: {
                    iCompanyId: {
                        required: 'This field is required.'
                    },
                    iFoodMenuId: {
                        required: 'This field is required.'
                    },
                    fPrice: {
                        required: 'This field is required.'
                    },
                    vSKU: {
                        remote: function () {
                            return errormessage;
                        }
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Food Menu Item Validation End
    jQuery.validator.addMethod("alphanumeric", function (value, element) {
        return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
    }, "Only letters and numbers are allowed");
    //Company Start
    if (_system_script == 'Company') {
        var errormessage;
        if ($('#_company_form').length !== 0) {
            $('#_company_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCompany: {required: true, minlength: 1, maxlength: 30},
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iCompanyId: function () {
                                    return $("#iCompanyId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vCaddress: {required: true, minlength: 2},
                    // vCity: {required: true},
                    // vState: {required: true},
                    vZip: {required: true, minlength: 2},
                    vLang: {required: true},
                    // vVatNum: {required: true, minlength: 2},
                    vCountry: {required: true}
                },
                messages: {
                    vCompany: {
                        required: 'This field is required.',
                        minlength: 'Company Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    // vCity: {
                    // required: 'City is required.'
                    // },
                    // vState: {
                    // required: 'State is required.'
                    // },
                    /*vVatNum: {
                     required: 'Vat Number is required.'
                     },*/
                    vCountry: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    $("#vCountry").prop('disabled', false);
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Company End


    /* Organization Module */

    if (_system_script == 'Organization') {
        var errormessage;
        if ($('#_organization_form').length !== 0) {
            $('#_organization_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {

                    vCompany: {required: true, minlength: 1, maxlength: 30},
                    vEmail: {
                        /* required: true, */
                        email: true,
                        /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {iOrganizationId: function () {
                                    return $("#iOrganizationId").val();
                                }},
                            dataFilter: function (response) {

                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },

                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true, //phonevalidate: true,
                        /* 06-09-219 check email,phone validation using member function added by Rs start(check phone number using country) */
                        /*remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {iOrganizationId: function () {
                                    return $("#iOrganizationId").val();
                                }},
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }*/
                    },
                    vCaddress: {required: true, minlength: 2},
                    // vCity: {required: true},
                    // vState: {required: true},
                    // vZip: {required: true, minlength: 2},
                    vLang: {required: true},
                    // vVatNum: {required: true, minlength: 2},
                    vCountry: {required: true},
                    ePaymentBy: {required: true},
                    iUserProfileMasterId: {required: true},
                    vImage: {accept: "jpg,jpeg,png,gif,bmp"}
                },
                messages: {
                    vCompany: {
                        required: 'This field is required.',
                        minlength: 'Organization Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        /*required: 'This field is required.',*/
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCaddress: {
                        required: 'This field is required.'
                    },
                    vZip: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    // vCity: {
                    // required: 'City is required.'
                    // },
                    // vState: {
                    // required: 'State is required.'
                    // },
                    /*vVatNum: {
                     required: 'Vat Number is required.'
                     },*/
                    vCountry: {
                        required: 'This field is required.'
                    },
                    ePaymentBy: {
                        required: 'This field is required.'
                    },
                    iUserProfileMasterId: {
                        required: 'This field is required.'
                    },

                    vImage: {accept: 'Please Upload valid file format for Image. Valid formats are jpg,jpeg,png,gif,bmp'}
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }

    /* Organization Module */


    //Rider Start
    if (_system_script == 'Rider') {
        var errormessage;
        if ($('#_rider_form').length !== 0) {
            $('#_rider_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vName: {required: true, minlength: 1, maxlength: 30},
                    vLastName: {required: true, minlength: 1, maxlength: 30},
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iUserId: function () {
                                    return $("#iUserId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vImgName: {required: false, accept: "image/*"},
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vCountry: {required: true},
                    // eGender: {required: true},
                    vPhone: {
                        required: true, minlength: 3, digits: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iUserId: function () {
                                    return $("#iUserId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone Number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone Number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vLang: {required: true},
                    vCurrencyPassenger: {required: true}
                },
                messages: {
                    vName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    vImgName: {accept: "Please select only image file."},
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    vCurrencyPassenger: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //rider End

    //make Start
    if (_system_script == 'Make') {
        if ($('#_make_form').length !== 0) {
            $('#_make_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vMake: {required: true, minlength: 2}
                },
                messages: {
                    vMake: {
                        required: 'This field is required.',
                        minlength: 'Make Name at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //make End

    //model Start
    if (_system_script == 'Model') {
        if ($('#_model_form').length !== 0) {
            $('#_model_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle: {required: true, minlength: 2}
                },
                messages: {
                    vTitle: {
                        required: 'This field is required.',
                        minlength: 'Model Name at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //model End

    //country Start
    //Country
    if (_system_script == 'country') {

        if ($('#_country_form').length !== 0) {
            $('#_country_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: {required: true, minlength: 2},
                    vCountryCode: {required: true, maxlength: 2},
                    vEmergencycode: {required: true, digits: true},
                    // vCountryCodeISO_3: {required: true},
                    vPhoneCode: {required: true},
                     vRImage:{
                        extension:imageUploadingExtenstionjsrule
                    },
                    vSImage:{
                        extension:imageUploadingExtenstionjsrule
                    },
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                        minlength: 'Country Name at least 2 characters long.'
                    },
                    vCountryCode: {
                        required: 'This field is required.',
                        minlength: 'Country Code Name at least 2 characters long.'
                    },
                    vRImage:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vSImage:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vPhoneCode: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //country End


    //State Start
    //State
    if (_system_script == 'state') {
        if ($('#_state_form').length !== 0) {
            $('#_state_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: {required: true},
                    vState: {required: true},
                    vStateCode: {required: true},
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                    },
                    vState: {
                        required: 'This field is required.'
                    },
                    vStateCode: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //State End


    //State Start
    if (_system_script == 'city') {
        if ($('#_city_form').length !== 0) {
            $('#_city_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vCountry: {required: true},
                    vState: {required: true},
                    vCity: {required: true},
                },
                messages: {
                    vCountry: {
                        required: 'This field is required.',
                    },
                    vState: {
                        required: 'This field is required.'
                    },
                    vCity: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //State End

    //faq Start
    if (_system_script == 'Faq') {
        //alert('hi');
        if ($('#_faq_form').length !== 0) {
            var vTitle_lang = 'vTitle_' + site_default_lang;
            $('#_faq_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length !== 0;
                            }
                        },
                        minlength: 2
                    },
                    vTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length === 0;
                            }
                        },
                        minlength: 2
                    },
                    tAnswer_Default: {
                        wysihtml5Required: {
                            id_of_field: "tAnswer",
                            Default_Lang: "_Default",
                        },
                    }
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                        minlength: 'English Question at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //faq End

    //FAQ_CAT Start
    if (_system_script == 'faq_categories') {
        if ($('#_faq_cat_form').length !== 0) {
            var vTitle_lang = 'vTitle_' + site_default_lang;
            $('#_faq_cat_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length !== 0;
                            }
                        },
                        minlength: 2
                    },
                    vTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length === 0;
                            }
                        },
                        minlength: 2
                    }
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                        minlength: 'English label at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //FAQ_CAT End

    //Pages Start

    if (_system_script == 'Pages' || _system_script == 'page') {
        if ($('#_page_form').length !== 0) {
            $('#_page_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vPageTitle_EN: {required: true, minlength: 2},
                    vImage1 :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vUserImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vDriverImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vSignImage1 :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vCompanyImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vStoreImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vSignImage2 :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vOrgImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    vHotelImage :{
                        extension: imageUploadingExtenstionjsrule
                    }
                    
                },

                messages: {
                    vPageTitle_EN: {
                        required: 'This field is required.',
                        minlength: 'PageTitle Value at least 2 characters long.'
                    },
                    vImage1 :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vUserImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vDriverImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vSignImage1 :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vCompanyImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vStoreImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vSignImage2 :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vOrgImage :{
                        extension: imageUploadingExtenstionMsg
                    },
                    vHotelImage :{
                        extension: imageUploadingExtenstionMsg
                    }


                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Pages End

    //Languages Start
    if (_system_script == 'language_label') {
        //alert('1111');
        if ($('#_languages_form').length !== 0) {
            $('#_languages_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vLabel: {required: true, minlength: 2},
                    vValue_Default: {required: true, minlength: 2}
                },
                messages: {
                    vLabel: {
                        required: 'This field is required.',
                        minlength: 'Language Label at least 2 characters long.'
                    },
                    vValue_Default: {
                        required: 'This field is required.',
                        minlength: 'English Value at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Languages End

    //Languages Other Label
    if (_system_script == 'language_label_other') {
        //alert('1111');
        if ($('#_language_label_other_form').length !== 0) {
            $('#_language_label_other_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vLabel: {required: true, minlength: 2},
                    vValue_EN: {required: true, minlength: 2}
                },
                messages: {
                    vLabel: {
                        required: 'This field is required.',
                        minlength: 'Language Label at least 2 characters long.'
                    },

                    vValue_EN: {
                        required: 'This field is required.',
                        minlength: 'English Value at least 2 characters long.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Languages Other Label


    //Driver Start
    if (_system_script == 'Driver') {
        var errormessage;
        if ($('#_driver_form').length !== 0) {
            $('#_driver_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vName: {required: true, minlength: 1, maxlength: 30},
                    vLastName: {required: true, minlength: 1, maxlength: 30},
                    vEmail: {
                        required: true, email: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_email.php',
                            type: "post",
                            data: {
                                iDriverId: function () {
                                    return $("#iDriverId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Email address is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Email address is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vPassword: {
                        required: function () {
                            return $("#actionOf").val() == "Add";
                        }, noSpace: true, minlength: 6, maxlength: 16
                    },
                    vPhone: {
                        required: true, minlength: 3, digits: true,
                        remote: {
                            url: _system_admin_url + 'ajax_validate_phone.php',
                            type: "post",
                            data: {
                                iDriverId: function () {
                                    return $("#iDriverId").val();
                                }
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                //response = $.parseJSON(response);
                                if (response == 'deleted') {
                                    errormessage = "Phone Number is Inactive/Deleted. Please active again.";
                                    return false;
                                } else if (response == 'false') {
                                    errormessage = "Phone Number is already exist.";
                                    return false;
                                } else {
                                    return true;
                                }
                            },
                            async: false
                        }
                    },
                    vImage: {required: false, accept: 'image/*'}, //, accept: 'image/*'
                    vCountry: {required: true},
                    //iCompanyId: {required: true},
                    //vZip: {required: true}, 
                    // eGender: {required: true},
                    // dBirthDate: {required: true},
                    vDay: {required: true},
                    vMonth: {required: true},
                    //vCaddress: {required: true},
                    vYear: {required: true},
                    vLang: {required: true},
                    vCurrencyDriver: {required: true},
                    vPaymentEmail: {required: false, email: true}
                },
                messages: {
                    vName: {
                        required: 'This field is required.',
                        minlength: 'First Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vLastName: {
                        required: 'This field is required.',
                        minlength: 'Last Name at least 2 characters long.',
                        maxlength: 'Please enter less than 30 characters.'
                    },
                    vEmail: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vPassword: {
                        required: 'This field is required.',
                        minlength: 'Password at least 6 characters long.',
                        maxlength: 'Please enter less than 16 characters.'
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                        remote: function () {
                            return errormessage;
                        }
                    },
                    vCountry: {
                        required: 'This field is required.'
                    },
                    /*iCompanyId: {
                     required: 'Company is required.'
                     },*/
                    /*vZip: {
                     required: 'Zip Code is required.'
                     },*/
                    /* dBirthDate: {
                     required: 'Birth Date is required.'
                     }, */
                    vDay: {
                        required: 'This field is required.'
                    },

                    vMonth: {
                        required: 'This field is required.'
                    },
                    vYear: {
                        required: 'This field is required.'
                    },
                    vLang: {
                        required: 'This field is required.'
                    },
                    /*vCaddress: {
                     required: 'Address is required.'
                     },*/
                    vCurrencyDriver: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Driver End

    //Vehicle Type Start
    /*    if (_system_script == 'VehicleType') {
     if ($('#_vehicleType_form').length !== 0) {
     $('#_vehicleType_form').validate({
     
     ignore: 'input[type=hidden]',
     errorClass: 'help-block',
     errorElement: 'span',
     errorPlacement: function (error, e) {
     e.parents('.row > div').append(error);
     },
     highlight: function (e) {
     $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
     $(e).closest('.help-block').remove();
     },
     success: function (e) {
     e.closest('.row').removeClass('has-success has-error');
     e.closest('.help-block').remove();
     e.closest('.help-inline').remove();
     },
     rules: {
     vVehicleType: {required: true},
     vVehicleType_EN: {required: true},
     fVisitFee: {required: true, number: true},
     fPricePerKM: {required: true, number: true},
     fPricePerMin: {required: true, number: true},
     fPricePerHour: {required: true, number: true},
     },
     messages: {
     vVehicleType: {
     required: 'Vehicle type is required.'
     },
     vVehicleType_EN: {
     required: 'Vehicle type (English) is required.'
     },
     fVisitFee: {
     required: 'Visit fee is required.'
     },
     fPricePerKM: {
     required: 'Price per KM is required.'
     },
     fPricePerMin: {
     required: 'Price per Minute is required.'
     },
     fPricePerHour: {
     required: 'Price per Hour is required.',
     minlength: 'dExpiryDate at least 2 characters long.'
     },
     }
     });
     }
     }*/
    //Vehicle Type End

    //Vehicle Type estimate fare Start
    if (_system_script == 'AdminFareEstimate') {
        if ($('#_vehicleType_esti_form').length !== 0) {
            $('#_vehicleType_esti_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iBaseFare: {required: true, number: true},
                    fPricePerKM: {required: true, number: true},
                    fPricePerMin: {required: true, number: true},
                    iMinFare: {required: true, number: true},
                    fCommision: {required: true, number: true},
                },
                messages: {
                    iBaseFare: {
                        required: 'This field is required.'
                    },
                    iMinFare: {
                        required: 'This field is required.'
                    },
                    fPricePerKM: {
                        required: 'This field is required.'
                    },
                    fPricePerMin: {
                        required: 'This field is required.'
                    },
                    fCommision: {
                        required: 'This field is required.'
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Vehicle Type estimate fare End

    //Cancel Reason
    if (_system_script == 'cancel_reason') {
        if ($('#_cancel_reason').length !== 0) {
            var vTitle_lang = 'vTitle_' + site_default_lang;
            $('#_cancel_reason').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    eType: {required: true},
                    eFor: {required: true},
                    vTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length !== 0;
                            }
                        }
                    },
                    vTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length === 0;
                            }
                        }
                    }
                },
                messages: {
                    eType: {
                        required: 'This field is required.',
                    },
                    eFor: {
                        required: 'This field is required.',
                    },
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Cancel Reason

    //Item Type
    if (_system_script == 'Cuisine') {
        if ($('#_cuisine_form').length !== 0) {
            var cuisineName_lang = 'cuisineName_' + site_default_lang;
            $('#_cuisine_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    cuisineName_Default: {
                        required: {
                            depends: function (element) {
                                return $('#cuisineName_Default').length !== 0;
                            }
                        },
                    },
                    cuisineName_lang: {
                        required: {
                            depends: function (element) {
                                return $('#cuisineName_Default').length === 0;
                            }
                        },
                    },
                    iServiceId: {required: true},
                },
                messages: {
                    cuisineName_Default: {
                        required: 'This field is required.',
                    },
                    iServiceId: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Item Type

    //Item Type
    if (_system_script == 'RatingFeedbackQuestions') {
        if ($('#_rating_feedback_form').length !== 0) {
            var cuisineName_lang = 'tQuestion_' + site_default_lang;
            $('#_rating_feedback_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    tQuestion_Default: {
                        required: {
                            depends: function (element) {
                                return $('#tQuestion_Default').length !== 0;
                            }
                        },
                    },
                    tQuestion_lang: {
                        required: {
                            depends: function (element) {
                                return $('#tQuestion_Default').length === 0;
                            }
                        },
                    }, 
                },
                messages: {
                    tQuestion_Default: {
                        required: 'This field is required.',
                    }, 
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Item Type

    //Delivery Preferences
    if (_system_script == 'DeliveryPreferences') {
        if ($('#_delivery_preference').length !== 0) {
            var tTitle_lang = 'tTitle_' + site_default_lang;
            $('#_delivery_preference').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    tTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#tTitle_Default').length !== 0;
                            }
                        },
                    },
                    tTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#tTitle_Default').length === 0;
                            }
                        },
                    },
                },
                messages: {
                    tTitle_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Delivery Preferences

    //Document Master
    if (_system_script == 'Document Master') {
        if ($('#_document_master').length !== 0) {
            var doc_name_lang = 'doc_name_' + site_default_lang;
            $('#_document_master').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    doc_type: {required: true},
                    country: {required: true},
                    exp: {required: true},
                    doc_name_Default: {
                        required: {
                            depends: function (element) {
                                return $('#doc_name_Default').length !== 0;
                            }
                        },
                    },
                    doc_name_lang: {
                        required: {
                            depends: function (element) {
                                return $('#doc_name_Default').length === 0;
                            }
                        },
                    },
                    iVehicleCategoryId: {
                        required: {
                            depends: function (element) {
                                return ($('#doc_type').val() == "driver" && $('input[name="eDocServiceType"]:checked').val() == "ServiceSpecific");
                            }
                        },
                    }
                },
                messages: {
                    doc_type: {
                        required: 'This field is required.',
                    },
                    country: {
                        required: 'This field is required.',
                    },
                    exp: {
                        required: 'This field is required.',
                    },
                    doc_name_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Document Master

    //Donation
    if (_system_script == 'donation') {
        if ($('#_donation_form').length !== 0) {
            var tTitle_lang = 'tTitle_' + site_default_lang;
            $('#_donation_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    tTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#tTitle_Default').length !== 0;
                            }
                        },
                    },
                    tTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#tTitle_Default').length === 0;
                            }
                        },
                    },
                    tLink: {required: true},
                },
                messages: {
                    tTitle_Default: {
                        required: 'This field is required.',
                    },
                    tLink: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Donation

    //Driver Subscription
    if (_system_script == 'DriverSubscriptionPlan') {
        if ($('#_subscription_form').length !== 0) {
            var vProfileName_lang = 'vPlanName_' + site_default_lang;
            var vPlanDescription_lang = 'vPlanDescription_' + site_default_lang;
            $('#_subscription_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vPlanName_Default: {
                        required: true,
                    },
                    vPlanDescription_Default: {
                        required: true,
                    },
                    vPlanPeriod: {required: true},
                    ePlanValidity: {required: true},
                    fPrice: {required: true, number: true},
                },
                messages: {
                    vPlanName_Default: {
                        required: 'This field is required.',
                    },
                    vPlanDescription_Default: {
                        required: 'This field is required.',
                    },
                    vPlanPeriod: {
                        required: 'This field is required.',
                    },
                    ePlanValidity: {
                        required: 'This field is required.',
                    },
                    fPrice: {
                        required: 'This field is required.',
                        number: 'Please enter a valid price.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Driver Subscription

    //Item Category
    
    if (_system_script == 'FoodMenu') {
        if ($('#food_category_form').length !== 0) {
            var vMenu_lang = 'vMenu_' + site_default_lang;
            $('#food_category_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iServiceId: {required: true},
                    iCompanyId: {required: true},
                    vMenu_Default: {
                        required: {
                            depends: function (element) {
                                return $('#vMenu_Default').length !== 0;
                            }
                        },
                        normalizer: function(value) {
                            return $.trim(value);
                        },
                    },
                    vMenu_lang: {
                        required: {
                            depends: function (element) {
                                return $('#vMenu_Default').length === 0;
                            }
                        },
                        normalizer: function(value) {
                            return $.trim(value);
                        },
                    },
                    vImage :{
                        extension: imageUploadingExtenstionjsrule
                    },
                    iDisplayOrder: {
                        required: true
                    }
                },
                messages: {
                    iServiceId: {
                        required: 'This field is required.',
                    },
                    iCompanyId: {
                        required: 'This field is required.',
                    },
                    vMenu_Default: {
                        required: 'This field is required.',
                    },
                    iDisplayOrder: {
                        required: 'This field is required.',
                    },
                    vImage: {
                        extension: imageUploadingExtenstionMsg
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Item Category

    //Help Topic
    if (_system_script == 'help_detail') {
        if ($('#_help_detail_form').length !== 0) {
            var vTitle_lang = 'vTitle_' + site_default_lang;
            $('#_help_detail_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    tAnswer_Default: {
                        required: {
                            depends: function (element) {
                                return $('#tAnswer_Default').length !== 0;
                            }
                        }
                    },
                    vTitle_Default: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length !== 0;
                            }
                        }
                    },
                    vTitle_lang: {
                        required: {
                            depends: function (element) {
                                return $('#vTitle_Default').length === 0;
                            }
                        }
                    }
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Help Topic

    //Help Topic
    if (_system_script == 'help_detail_categories') {
        if ($('#_help_detail_cat_form').length !== 0) {
            $('#_help_detail_cat_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {required: true},
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Help Topic

    //News
    if (_system_script == 'news') {
        if ($('#_news_form').length !== 0) {
            $('#_news_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    eUserType: {required: true},
                    vTitle_Default: {required: true},
                    vNewfeedImage:{extension: imageUploadingExtenstionjsrule}
                },
                messages: {
                    eUserType: {
                        required: 'This field is required.',
                    },
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                    vNewfeedImage:{extension: imageUploadingExtenstionMsg}
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //News

    //Order Status
    if (_system_script == 'order_status') {
        if ($('#order_status_action').length !== 0) {
            $('#order_status_action').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vStatus_Default: {required: true},
                    vStatus_Track_Default: {required: true},
                },
                messages: {
                    vStatus_Default: {
                        required: 'This field is required.',
                    },
                    vStatus_Track_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Order Status

    //Package Type
    if (_system_script == 'Package') {
        if ($('#_package_type').length !== 0) {
            $('#_package_type').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vName_Default: {required: true,OnlySpaceNotAllowed: true},
                    /* vImage :{
                        required: true,
                        extension: imageUploadingExtenstionjsrule
                    } */
                    //iDeliveryFieldId: {required: true},
                },
                messages: {
                    vName_Default: {
                        required: requiredFieldMsg,
                    },
                    /* vImage:{
                        required: requiredFieldMsg,
                        extension: imageUploadingExtenstionMsg
                    } */
                    /*iDeliveryFieldId: {
                        required: 'This field is required.',
                    },*/
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Package Type

    //SMS Template
    if (_system_script == 'sms_templates') {
        if ($('#_sms_template_form').length !== 0) {
            $('#_sms_template_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vSubject_Default: {required: true},
                    vBody_Default: {required: true},
                },
                messages: {
                    vSubject_Default: {
                        required: 'This field is required.',
                    },
                    vBody_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //SMS Template

    //Store Vehicle Type
    if (_system_script == 'StoreVehicleType') {
        if ($('#_store_vehicleType_form').length !== 0) {
            $('#_store_vehicleType_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vVehicleType_Default: {
                        required: true, normalizer: function (value) {
                            return $.trim(value);
                        },
                    },
                    iLocationId: {required: true},
                    fRadius: {required: true, number: true},
                    fCommision: {required: true, number: true},
                },
                messages: {
                    vVehicleType_Default: {
                        required: 'This field is required.',
                    },
                    iLocationId: {
                        required: 'This field is required.',
                    },
                    fRadius: {
                        required: 'This field is required.',
                        number: 'Please enter a valid radius.',
                    },
                    fCommision: {
                        required: 'This field is required.',
                        number: 'Please enter a valid commission.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Store Vehicle Type

    //Trip Reason
    if (_system_script == 'BusinessTripReason') {
        if ($('#_trip_reason').length !== 0) {
            $('#_trip_reason').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iUserProfileMasterId: {required: true},
                    vReasonTitle_Default: {required: true},
                },
                messages: {
                    iUserProfileMasterId: {
                        required: 'This field is required.',
                    },
                    vReasonTitle_Default: {
                        required: 'This field is required.',
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Trip Reason

    //Ride Profile Type
    if (_system_script == 'RideProfileType') {
        if ($('#_rideProfile_form').length !== 0) {
            $('#_rideProfile_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vProfileName_Default: {required: true},
                    vTitle_Default: {required: true},
                    vSubTitle_Default: {required: true},
                    vScreenHeading_Default: {required: true},
                    vScreenTitle_Default: {required: true},
                    vScreenButtonText_Default: {required: true},
                    vShortProfileName_Default: {required: true},
                    tDescription_Default: {required: true},
                    vImage: {extension: imageUploadingExtenstionjsrule},
                    vWelcomeImage: { extension: imageUploadingExtenstionjsrule}
                },
                messages: {
                    vProfileName_Default: {
                        required: 'This field is required.',
                    },
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                    vSubTitle_Default: {
                        required: 'This field is required.',
                    },
                    vScreenHeading_Default: {
                        required: 'This field is required.',
                    },
                    vScreenTitle_Default: {
                        required: 'This field is required.',
                    },
                    vScreenButtonText_Default: {
                        required: 'This field is required.',
                    },
                    vShortProfileName_Default: {
                        required: 'This field is required.',
                    },
                    tDescription_Default: {
                        required: 'This field is required.',
                    },
                     vImage:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vWelcomeImage:{
                        extension: imageUploadingExtenstionMsg
                    }
                    
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Ride Profile Type

    //reward Start

    if (_system_script == 'Reward') {
    
        var formid = 0;
        $('input:submit').click(function () {
            formid = $(this).attr('attr-formid');
            if ($('#_reward_settings_' + formid).length !== 0) {
            
                $('#_reward_settings_' + formid).validate({
                
                    ignore: 'input[type=hidden]',
                
                    errorClass: 'help-block',
                
                    errorElement: 'span',
                
                    errorPlacement: function (error, e) {
    
                        if (e.attr("name") === "vMinimumTrips" || e.attr("name") === "fRatings" || e.attr("name") === "iAcceptanceRate" || e.attr("name") === "iCancellationRate" ) {
                        
                         
                            $("#require_from_group_error_" + formid).html(error);
    
                        } else {
                            e.parents('div.input-group').after(error);
                        }
                    
                    },
                
                    highlight: function (e) {
                    
                        $(e).closest('div.input-group').removeClass('has-success has-error').addClass('has-error');
                    
                        $(e).closest('.help-block').remove();
                    
                    },
                
                    success: function (e) {
                        e.closest('.row').find('.input-group').removeClass('has-error');
                        e.closest('div.input-group').removeClass('has-success has-error');
                    
                        e.closest('.help-block').remove();
                    
                        e.closest('.help-inline').remove();
                    
                    },
                
                    rules: {
                    
                        vLevel: {required: true},

                        vMinimumTrips: {
                            require_from_group: [1, ".reward_setting_"+formid],
                            allowZeroForGroupValidation : formid,min: 0
                        },
                        fRatings: {require_from_group: [1, ".reward_setting_"+formid] ,allowZeroForGroupValidation : formid,min: 0},
                        iAcceptanceRate: {require_from_group: [1, ".reward_setting_"+formid] , allowZeroForGroupValidation : formid ,min: 0},
                        iCancellationRate: {require_from_group: [1, ".reward_setting_"+formid] , allowZeroForGroupValidation : formid ,min: 0},
                        iOnlineHours: {require_from_group: [1, ".reward_setting_"+formid] , allowZeroForGroupValidation : formid ,min: 0},
                        fCredit: {required: true,min: 0},
                        // vImage: {required: true},
                        // vImage: {extension: imageUploadingExtenstionjsrule}
                        // vImage: {require_from_group: [1, ".reward_image_"+formid] , extension : imageUploadingExtenstionjsrule},
                    
                    
                    },
                
                    messages: {
                    
                        vLevel: {
                            required: 'This field is required.'
                        },
                    
                        vMinimumTrips: {
                            required: 'This field is required.'
                        },
                    
                        fRatings: {
                            required: 'This field is required.'
                        },
                    
                        iAcceptanceRate: {
                            required: 'This field is required.'
                        },
                    
                        iCancellationRate: {
                            required: 'This field is required.'
                        },
                    
                        iDuration: {
                            required: 'This field is required.'
                        },
                    
                        fCredit: {
                            required: 'This field is required.'
                        },
                        vImage : {
                            required: 'Image field is required.',
                            extension: imageUploadingExtenstionMsg 
                        },
                        groups: {
                            require_from_group: "srdjfdjg"
                        }
                    },
                
                    submitHandler: function (form) {
                    
                        if ($(form).valid())
                        
                            form.submit();
                    
                        return false; // prevent normal form posting
                    
                    }
                
                });
            
            }
        });

    }


    if (_system_script == 'masterServiceMenu') {

        var formid = 0;
        $('input:submit').click(function () {
            formid = $(this).attr('attr-formid');
            if ($('#_list_form' + formid).length !== 0) {

                $('#_list_form' + formid).validate({

                    ignore: 'input[type=hidden]',

                    errorClass: 'help-block',

                    errorElement: 'span',

                    errorPlacement: function (error, e) {

                        e.parents('div.input-group').append(error);

                    },

                    highlight: function (e) {

                        $(e).closest('div.input-group').removeClass('has-success has-error').addClass('has-error');

                        $(e).closest('.help-block').remove();

                    },

                    success: function (e) {

                        e.closest('div.input-group').removeClass('has-success has-error');

                        e.closest('.help-block').remove();

                        e.closest('.help-inline').remove();

                    },

                    rules: {

                        // vLevel: { required: true },

                    },

                    messages: {

                        // vLevel: {
                        //     required: 'This field is required.'
                        // },

                    },

                    submitHandler: function (form) {

                        if ($(form).valid())
                            saveMenuText(formid);
                        //form.submit();

                        return false; // prevent normal form posting

                    }

                });

            }
        });

    }

    //Trip Reason
    
    // console.log(_system_script);
    // if (_system_script == 'VehicleCategory_Ride') {
    //     // if ($('#vtype').length !== 0) {
    //         $('#vtype').validate({
    //             ignore: 'input[type=hidden]',
    //             errorClass: 'help-block',
    //             errorElement: 'span',
    //             errorPlacement: function (error, e) {
    //                 console.log("error");
    //                 e.parents('.row > div').append(error);
    //             },
    //             highlight: function (e) {
    //                 $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
    //                 $(e).closest('.help-block').remove();
    //             },
    //             success: function (e) {
    //                 e.closest('.row').removeClass('has-success has-error');
    //                 e.closest('.help-block').remove();
    //                 e.closest('.help-inline').remove();
    //                 console.log("success");
    //             },
    //             rules: {
    //                 // vBannerTitle: {required: true,OnlySpaceNotAllowed: true},
    //                 // dExpiryDate: {dateGreaterThan: true},
    //                 vListLogo3:{
    //                     required: true,
    //                     extension: imageUploadingExtenstionjsrule
    //                 }

    //             },
    //             messages: {
    //                 vListLogo3:{
    //                     extension: imageUploadingExtenstionMsg
    //                 }
    //                 // ,
    //                 // vBannerTitle: {
    //                 //     required: 'This field is required.',
    //                 // },
    //             },
    //             submitHandler: function (form) {
    //                 if ($(form).valid()) {
    //                     // ShpSq6fAm7(form);
    //                     form.submit();
    //                 }
    //                 return false; // prevent normal form posting
    //             }
    //         });
    //     // }
    // }

    if (_system_script == 'Advertisement Banners' && _system_script != 'VehicleCategory_Ride') {
        if ($('#vtype').length !== 0) {
            $('#vtype').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vBannerTitle: {required: true,OnlySpaceNotAllowed: true},
                    dExpiryDate: {dateGreaterThan: true},
                    vBannerImage:{extension:imageUploadingExtenstionjsrule},
                    vHomepageLogoOurServices:{ extension:imageUploadingExtenstionjsrule}

                },
                messages: {
                    vBannerImage:{
                        extension: imageUploadingExtenstionMsg
                    },
                    vBannerTitle: {
                        required: 'This field is required.',
                    },
                    vHomepageLogoOurServices:{extension: imageUploadingExtenstionMsg}
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Trip Reason
    /*--------------------- nearByPlaces  ------------------*/
    if (_system_script == 'nearbyPlaces') {
        if ($('#nearBy_Places_action').length !== 0) {
            $('#nearBy_Places_action').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    
                    if(e.hasClass('slotTime'))
                    {
                        error.insertAfter( e.parent());
                    }else{
                        error.insertAfter(e);
                    }
                    
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle: {required: true,OnlySpaceNotAllowed: true},
                    vPlacesLocation: {required: true,OnlySpaceNotAllowed: true},
                    vAddress: {required: true,OnlySpaceNotAllowed: true},
                    vPhone: {required: true,OnlySpaceNotAllowed: true,minlength: 3, digits: true},
                    vImage: {
                        extension: "png|jpg|jpeg|gif|svg",
                    },
                    // iCompanyId: {required: true},
                    iNearByCategoryId: {required: true},
                    //  vOfferDiscount: {required: true},
                    iNearByCategoryId: {required: true},
                    // vImage: {required: true},
                    vAboutPlaces: {required: true,OnlySpaceNotAllowed: true},
                    vCountry: {required: true},
                    vImage :{
                        extension:imageUploadingExtenstionjsrule
                    }
                },
                messages: {
                    vTitle: {
                        required: 'This field is required.',
                    },
                    vAddress: {
                        required: 'This field is required.',
                    },
                    vPhone: {
                        required: 'This field is required.',
                        minlength: 'Please enter at least three Number.',
                        digits: 'Please enter proper mobile number.',
                    },
                    vImage :{
                        extension: imageUploadingExtenstionMsg
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }

    if (_system_script == 'nearbyCategory') {
        if ($('#nearby_category_form').length !== 0) {
            $('#nearby_category_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    // e.parents('.row > div').append(error);
                    error.insertAfter(e);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {required: true},
                    vImage:{
                            required: function () {
                                if($("#vImage_old").val() != ""){
                                    return false;
                                }else {
                                    return true;
                                }
                        },
                        extension:imageUploadingExtenstionjsrule

                    }
                    // vImage: function () {
                    //     if($("#vImage_upload").val() == 1){
                    //         return false;
                    //     }else {
                    //         return true;
                    //     }
                        
                    // },


                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                    vImage: {
                        required: requiredFieldMsg,
                        extension: imageUploadingExtenstionMsg
                    },
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    /*--------------------- nearByPlaces  ------------------*/

    // Driver Details Field
    if (_system_script == 'RideShareDriverFields') {
        if ($('#_driver_field_form').length !== 0) {
            $('#_driver_field_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vFieldName: {required: true},
                    tFieldName_Default: {required: true}
                },
                messages: {
                    vFieldName: {
                        required: 'This field is required.',
                    },
                    tFieldName_Default: {
                        required: 'This field is required.',
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    // Driver Details Field

    // Track Service Vehicles Start
    if (_system_script == 'TrackServiceDriverVehicle') {
        if ($('#_track_service_vehicle_form').length !== 0) {
            $('#_track_service_vehicle_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    iMakeId: {required: true},
                    iModelId: {required: true},
                    iYear: {required: true},
                    vLicencePlate: {required: true},
                    iCompanyId: {required: true},
                    iDriverId: {required: true},
                },
                messages: {
                    iMakeId: {
                        required: 'This field is required.'
                    },
                    iModelId: {
                        required: 'This field is required.'
                    },
                    iYear: {
                        required: 'This field is required.'
                    },
                    vLicencePlate: {
                        required: 'This field is required.'
                    },
                    iCompanyId: {
                        required: 'This field is required.'
                    },
                    iDriverId: {
                        required: 'This field is required.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    // Track Service Vehicles End

    //Coupon Start
    if (_system_script == 'GiftCard') {
        if ($('#_gift_card_form').length !== 0) {
            var tDescription_lang = 'tDescription_' + site_default_lang;
            $('#_gift_card_form').validate({

                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vGiftCardCode: {
                        required: true,
                        remote: {
                            url: _system_admin_url + 'gift_card_action.php',
                            type: "post",
                            data: {
                                vGiftCardCode: function () {
                                    return $("#vGiftCardCode").val();
                                },
                                iGiftCardId: function () {
                                    return $("#iGiftCardId").val();
                                },
                                method: "checkDuplicateCode"
                            },
                            beforeSend : function(xhr) {
                                mO4u1yc3dx(xhr);
                            },
                            dataFilter: function (response) {
                                if (response == '0') {
                                    errormessage = "This code already Exist.";
                                    return false;
                                } else if (response == '1') {
                                    return true;
                                } else {
                                    return true;
                                }
                            },
                            async: true
                        }
                    },
                    fAmount: {
                        required: true,
                        number: true,
                        notZero: true
                    },
                    iUserId: {
                        required: {
                            depends: function (element) {
                                return $('#eUserType').val() == "UserSpecific";
                            }
                        }
                    },
                    iDriverId: {
                        required: {
                            depends: function (element) {
                                return $('#eUserType').val() == "DriverSpecific";
                            }
                        }
                    }
                },
                messages: {
                    vGiftCardCode: {
                        required: 'This field is required.',
                        remote: function () {
                            return errormessage;
                        },
                    },
                    fAmount: {
                        required: 'This field is required.',
                        number: 'Please enter a valid amount.',
                        notZero: 'Please enter a valid amount.'
                    }
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    //Coupon End
    
    
    /*------------------ride share-----------------*/
    if (_system_script == 'travel_preferences_category') {
        if ($('#_TravelPreferencesCategory_cat_form').length !== 0) {
            $('#_TravelPreferencesCategory_cat_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {required: true},
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                   
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    
    if (_system_script == 'Travel_Preferences') {
        if ($('#_travel_preferences_form').length !== 0) {
            $('#_travel_preferences_form').validate({
                ignore: 'input[type=hidden]',
                errorClass: 'help-block',
                errorElement: 'span',
                errorPlacement: function (error, e) {
                    e.parents('.row > div').append(error);
                },
                highlight: function (e) {
                    $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                    $(e).closest('.help-block').remove();
                },
                success: function (e) {
                    e.closest('.row').removeClass('has-success has-error');
                    e.closest('.help-block').remove();
                    e.closest('.help-inline').remove();
                },
                rules: {
                    vTitle_Default: {required: true},
                },
                messages: {
                    vTitle_Default: {
                        required: 'This field is required.',
                    },
                    
                },
                submitHandler: function (form) {
                    if ($(form).valid()) {
                        ShpSq6fAm7(form);
                        form.submit();
                    }
                    return false; // prevent normal form posting
                }
            });
        }
    }
    /*------------------ride share-----------------*/
    /*------------------GEO Location From Validation-----------------*/
    if ($('#location_form').length !== 0) {
        
        $('#location_form').validate({
            ignore: 'input[type=hidden]',
            errorClass: 'help-block',
            errorElement: 'span',
            errorPlacement: function (error, e) {
                e.parents('.row > div').append(error);
            },
            highlight: function (e) {
                $(e).closest('.row').removeClass('has-success has-error').addClass('has-error');
                $(e).closest('.help-block').remove();
            },
            success: function (e) {
                e.closest('.row').removeClass('has-success has-error');
                e.closest('.help-block').remove();
                e.closest('.help-inline').remove();
            },
            rules: {
                vLocationName: {required: true},
                iCountry: {required: true},
                eFor: {required: true},
            },
            messages: {
                vLocationName: {
                    required: 'This field is required.',
                },
                iCountry: {
                    required: 'This field is required.',
                },
                eFor: {
                    required: 'This field is required.',
                },
                
            },
            submitHandler: function (form) {
            
                
                if ($(form).valid() && IsEmpty()) {
                    ShpSq6fAm7(form);
                    form.submit();
                }
                return false; // prevent normal form posting
            }
        });
    }
    /*------------------GEO Location From Validation-----------------*/
    
    
    /*------------------Rental Package Form Validation-----------------*/
    $('#rental_package').validate({
        rules: {
            vPackageName_Default: {
                required: true
            }, fPrice: {
                required: true
                
            }, fKiloMeter: {
                required: true
            }, fHour: {
                required: true
            }, fPricePerKM: {
                required: true
            }, fPricePerHour: {
                required: true
            }
        }
    });
    /*------------------Rental Package Form Validation-----------------*/
    
    
});



/*------------------Apply the "numericInput" class when you want to allow only numbers in the field-----------------*/

document.addEventListener('DOMContentLoaded', function () {
    var numericInputElements = document.querySelectorAll('.numericInputA');
    numericInputElements.forEach(function (input) {
        input.addEventListener('input', function (event) {
            var inputValue = event.target.value;
            var numericValue = inputValue.replace(/[^0-9]/g, '');
            event.target.value = numericValue;
        });
    });
});

/*------------------Apply the "numericInput" class when you want to allow only numbers in the field-----------------*/