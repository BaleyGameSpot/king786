
<script>


    function show_rider_details(userid) {
        $("#detail_modal1 , #detail_modal2").modal('hide');
        $("#rider_detail").html('');
        $(".imageIcons,#imageIcons").show();
        $("#detail_modal").modal('show');
        if (userid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': "iUserId=" + userid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#rider_detail").html(data);
                    $(".imageIcons,#imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#detail_modal").modal('hide');
                }
            });
        }
    }

    function show_driver_details(driverid) {
        $("#detail_modal , #detail_modal2 ").modal('hide');
        $("#driver_detail").html('');
        $("#imageIcons1").show();
        $("#detail_modal1").modal('show');

        if (driverid != "") {
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_driver_details.php',
                'AJAX_DATA': "iDriverId=" + driverid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#imageIcons1").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons1").hide();
                }
            });
        }
    }

    function show_company_details(companyid) {
        $("#detail_modal1 , #detail_modal ").modal('hide');
        $("#comp_detail").html('');
        $("#imageIcons2").show();
        $("#detail_modal2").modal('show');

        if (companyid != "") {

            var ajaxData = {
                'URL': "<?= $tconfig['tsite_url_main_admin'] ?>ajax_company_details.php",
                'AJAX_DATA': "iCompanyId=" + companyid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#comp_detail").html(data);
                    $("#imageIcons2").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons2").hide();
                }
            });
        }
    }
    function show_store_details(companyid) {
        $("#detail_modal1 , #detail_modal, #detail_modal2 ").modal('hide');
        $("#store_detail").html('');
        $("#imageIcons4").show();
        $("#detail_modal4").modal('show');

        if (companyid != "") {

            var ajaxData = {
                'URL': "<?= $tconfig['tsite_url_main_admin'] ?>ajax_store_details.php",
                'AJAX_DATA': "iCompanyId=" + companyid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#store_detail").html(data);
                    $("#imageIcons4").hide();
                } else {
                    console.log(response.result);
                    $("#imageIcons4").hide();
                }
            });
        }
    }

	function show_org_details(iOrganizationId) {
		$("#org_detail").html('');
		$("#imageIcons3").show();
		$("#detail_modal3").modal('show');

		if (iOrganizationId != "") {
			
			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_organization_details.php',
				'AJAX_DATA': {"iOrganizationId": iOrganizationId},
				'REQUEST_DATA_TYPE': 'html'
			};
			getDataFromAjaxCall(ajaxData, function(response) {
				if(response.action == "1") {
					var data = response.result;
					$("#org_detail").html(data);
					$("#imageIcons3").hide();  
				}
				else {
					console.log(response.result);
				}
			});
		}
	}
	

	function show_track_company_details(companyid) {
		$("#track_comp_detail").html('');
		$("#track_company_imageIcons").show();
		$("#track_company_detail_modal").modal('show');
		if (companyid != "") {
			var ajaxData = {
				'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_company_details.php',
				'AJAX_DATA': "iCompanyId=" + companyid,
				'REQUEST_DATA_TYPE': 'html'
			};
			getDataFromAjaxCall(ajaxData, function (response) {
				if (response.action == "1") {
					var data = response.result;
					$("#track_comp_detail").html(data);
					$("#track_company_imageIcons").hide();
				}
				else {
					console.log(response.result);
					$("#track_company_imageIcons").hide();
				}
			});
		}
	} 
		
	function show_track_rider_details(userid) {
			$("#track_rider_detail").html('');
			$("#track_rider_imageIcons").show();
			$("#track_rider_detail_modal").modal('show');

			if (userid != "") { 
				var ajaxData = {
					'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_track_rider_details.php',
					'AJAX_DATA': "iUserId=" + userid,
					'REQUEST_DATA_TYPE': 'html'
				};
				getDataFromAjaxCall(ajaxData, function (response) {
					if (response.action == "1") {
						var data = response.result;
						$("#track_rider_detail").html(data);
						$("#track_rider_imageIcons").hide();
					} else {
						console.log(response.result);
						$("#track_rider_imageIcons").modal('hide');
					}
				});
			}
		}
    function languageLabel(label){
        href = "<?= $tconfig['tsite_url_main_admin'] ?>languages.php?keywords=" + label;
        window.open(href);

    }

    function show_publisher_details(userid) {
        $("#driver_detail").html('');
        $("#driver_imageIcons").show();
        $("#driver_modal").modal('show');

        if (userid != "") { 
            var ajaxData = {
                'URL': '<?= $tconfig['tsite_url_main_admin'] ?>ajax_rider_details.php',
                'AJAX_DATA': "iUserId=" + userid,
                'REQUEST_DATA_TYPE': 'html'
            };
            getDataFromAjaxCall(ajaxData, function (response) {
                if (response.action == "1") {
                    var data = response.result;
                    $("#driver_detail").html(data);
                    $("#driver_imageIcons").hide();
                } else {
                    console.log(response.result);
                    $("#driver_modal").modal('hide');
                }
            });
        }
    }

</script>