function openRequirementsModal(modal_id) {
    $('body').css('overflow', 'hidden');
    //console.log(modal_id);
    $('#' + modal_id).modal('show');
}

function closeRequirementsModal(modal_id) {
    $('#' + modal_id).modal('hide');
    $('body').css('overflow', 'auto');
}

//var server_requirements = ['folder_permissions', 'cron_jobs_status', 'system_settings', 'mysql_suggestions', 'mysql_settings', 'php_modules', 'phpini_settings', 'server_ports', 'server_settings'];
var server_requirements = ['folder_permissions', 'mysql_suggestions', 'server_ports'];

var server_requirements_error = 0;
function ajaxRequest (server_requirements) {
	
    if(server_requirements.length > 0) 
    {
        
    	var server_requirement = server_requirements.pop();
       // console.log(server_requirement);
        var show_all_missing = $('#show_all_missing').val();
        $.ajax({
            type: 'POST',
            url: 'ajax_check_server_requirements.php?time=' + new Date().getTime(),
            dataType: 'json',
            data: {'server_requirement': server_requirement, 'SHOW_ALL_MISSING': show_all_missing},
            beforeSend : function(xhr) {
                mO4u1yc3dx(xhr);
            },
            success: function (response) {
                //  alert(response);
            	if(server_requirement == "server_settings")
            	{
            		$('#server_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
            			$('#server_settings_content').find('button').removeAttr('onclick');
            			$('#server_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#server_settings_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#server_settings_content').find('.status').html('Running');
                        $('#server_settings_content').find('.icon').addClass("server-success"); 
            		}
            		else {
            			$('#server_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#server_settings_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#server_settings_content').find('.status').html('Missing');
                        $('#server_settings_content').find('.icon').addClass("server-danger");
            		}
            		$('#server_settings_content').find('button').show();
            	}
            	else if(server_requirement == "phpini_settings") {
            		$('#phpini_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#phpini_settings_content').find('button').removeAttr('onclick');
            			$('#phpini_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#phpini_settings_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#phpini_settings_content').find('.status').html('Running');
                        $('#phpini_settings_content').find('.icon').addClass("server-success"); 
            		}
            		else {
            			$('#phpini_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#phpini_settings_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#phpini_settings_content').find('.status').html('Missing');
                        $('#phpini_settings_content').find('.icon').addClass("server-danger");
            		}
            		$('#phpini_settings_content').find('button').show();
            	}
            	else if(server_requirement == "php_modules") {
            		$('#php_modules_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#php_modules_content').find('button').removeAttr('onclick');
            			$('#php_modules_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#php_modules_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#php_modules_content').find('.status').html('Running');
                        $('#php_modules_content').find('.icon').addClass("server-success"); 

            		}
            		else {
            			$('#php_modules_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#php_modules_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#php_modules_content').find('.status').html('Missing');
                        $('#php_modules_content').find('.icon').addClass("server-danger"); 
            		}
            		$('#php_modules_content').find('button').show();
            	}
            	else if(server_requirement == "mysql_settings") {
            		$('#mysql_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#mysql_settings_content').find('button').removeAttr('onclick');
            			$('#mysql_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#mysql_settings_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#mysql_settings_content').find('.status').html('Running');
                        $('#mysql_settings_content').find('.icon').addClass("server-success"); 
            		}
            		else {
            			$('#mysql_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#mysql_settings_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#mysql_settings_content').find('.status').html('Missing');
                        $('#mysql_settings_content').find('.icon').addClass("server-danger"); 
            		}
            		$('#mysql_settings_content').find('button').show();
            	}
            	else if(server_requirement == "server_ports") {
            		$('#server_ports_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#server_ports_content').find('button').removeAttr('onclick');
            			$('#server_ports_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#server_ports_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#server_ports_content').find('.status').html('Running');
                        $('#server_ports_content').find('.icon').addClass("server-success"); 
            		}
            		else {
            			$('#server_ports_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#server_ports_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#server_ports_content').find('.status').html('Missing');
                        $('#server_ports_content').find('.icon').addClass("server-danger");
                        $('#server_ports_modal').find('.requirement-list').html(response.server_requirement_html);
            		}

                    $('#server_ports_modal').find('.requirement-list').html(response.server_requirement_html);
                    $('#requirements_modal').find('.server-ports-content').html(response.all_ports_html);
            		$('#server_ports_content').find('button').show();
            	}
                else if(server_requirement == "cron_jobs_status") {
                    $('#cron_jobs_status_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#cron_jobs_status_content').find('button').removeAttr('onclick');
                        $('#cron_jobs_status_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#cron_jobs_status_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#cron_jobs_status_content').find('.status').html('Running');
                        $('#cron_jobs_status_content').find('.icon').addClass("server-success"); 
                    }
                    else {
                        $('#cron_jobs_status_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#cron_jobs_status_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Failed');
                        $('#cron_jobs_status_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#cron_jobs_status_content').find('.status').html('Missing');
                        $('#cron_jobs_status_content').find('.icon').addClass("server-danger"); 
                    }
                    $('#cron_jobs_status_content').find('button').show();
                }
                else if(server_requirement == "mysql_suggestions") {
                    $('#mysql_suggestions_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#mysql_suggestions_content').find('button').removeAttr('onclick');
                        $('#mysql_suggestions_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#mysql_suggestions_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#mysql_suggestions_content').find('.status').html('No Suggestions');
                        $('#mysql_suggestions_content').find('.icon').addClass("server-success"); 
                    }
                    else {
                        $('#mysql_suggestions_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#mysql_suggestions_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#mysql_suggestions_content').find('.status').html('Missing');
                        $('#mysql_suggestions_content').find('.icon').addClass("server-danger"); 

                        
                    }
                    $('#mysql_suggestions_content').find('button').show();
                }
                else if(server_requirement == "folder_permissions") {
                    $('#folder_permissions_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#folder_permissions_content').find('button').removeAttr('onclick');
                        $('#folder_permissions_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#folder_permissions_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#folder_permissions_content').find('.status').html('Correct');
                        $('#folder_permissions_content').find('.icon').addClass("server-success"); 

                    }
                    else {
                        $('#folder_permissions_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#folder_permissions_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#folder_permissions_content').find('.status').html('View');
                        $('#folder_permissions_content').find('.icon').addClass("server-danger"); 

                        $('#folder_permissions_modal').find('.requirement-list').html(response.server_requirement_html);
                    }

                    $('#folder_permissions_modal').find('.requirement-list').html(response.server_requirement_html);
                    $('#requirements_modal').find('.folder-permissions-content').html(response.server_requirement_html);
                    $('#folder_permissions_content').find('button').show();
                }
                else if(server_requirement == "system_settings") {
                    $('#system_settings_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#system_settings_content').find('button').removeAttr('onclick');
                        $('#system_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#system_settings_content').find('.icon').html('<i class="fa fa-check"></i>');
                        $('#system_settings_content').find('.status').html('Correct');
                        $('#system_settings_content').find('.icon').addClass("server-success");
                    }
                    else {
                        $('#system_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#system_settings_content').find('.icon').html('<i class="fa fa-exclamation-triangle"></i>');
                        $('#system_settings_content').find('.status').html('View');
                        $('#system_settings_content').find('.icon').addClass("server-danger");
                    }
                    $('#system_settings_content').find('button').show();
                }

                if(response.Action == 0)
                {
                    server_requirements_error = 1;
                }
            }
        })
        .done(function (result) {
         
            ajaxRequest(server_requirements);
           
        });
    }
    else {
        $('#view_server_requirements').show();
        if(server_requirements_error == 0)
        {
            $(".blocks, .server-requirements-note").hide();
            $(".toggle-server-requirements").find('i').removeClass('fa-minus').addClass('fa-plus');
        }
    }
}

$(document).ready(function() {
    $('#view_server_requirements').hide();
	ajaxRequest(server_requirements);
});

$('.copy-value').click(function() {
    var element = $(this).closest('li').find('.suggested-value');
    copyToClipboard(element);
});

function copyToClipboard(element) {
    element.select();
    document.execCommand("copy");
}

$('.toggle-server-requirements').click(function() {
    if($(".blocks").css('display') == "none")
    {
        $(".blocks, .server-requirements-note").show();
        $(this).find('i').removeClass('fa-plus').addClass('fa-minus'); 
    }
    else {
        $(".blocks, .server-requirements-note").hide();
        $(this).find('i').removeClass('fa-minus').addClass('fa-plus');
    }
});

$('body').on('hidden.bs.modal', function () {
    $('body').css('overflow', 'auto');
});

$('.requirement-list').mCustomScrollbar({
    theme:"minimal-dark",
    scrollInertia: 300
});