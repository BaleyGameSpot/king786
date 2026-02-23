function openRequirementsModal(modal_id) {
    $('body').css('overflow', 'hidden');
    $('#' + modal_id).modal('show');
}

function closeRequirementsModal(modal_id) {
    $('#' + modal_id).modal('hide');
    $('body').css('overflow', 'auto');
}

var server_requirements = ['folder_permissions', 'cron_jobs_status', 'system_settings', 'mysql_suggestions', 'mysql_settings', 'php_modules', 'phpini_settings', 'server_ports', 'server_settings'];

var server_requirements_error = 0;
function ajaxRequest (server_requirements) {
	
    if(server_requirements.length > 0) 
    {
    	var server_requirement = server_requirements.pop();
        var show_all_missing = $('#show_all_missing').val();
        $.ajax({
            type: 'POST',
            url: 'ajax_check_server_requirements.php?time=' + new Date().getTime(),
            dataType: 'json',
            data: {'server_requirement': server_requirement, 'SHOW_ALL_MISSING': show_all_missing},
            success: function (response) {
                // alert(response);
            	if(server_requirement == "server_settings")
            	{
            		$('#server_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
            			$('#server_settings_content').find('button').removeAttr('onclick');
            			$('#server_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#server_settings_content').find('button').html('<i class="fa fa-check"></i>Running');
            		}
            		else {
            			$('#server_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
            			$('#server_settings_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Missing');
            		}
            		$('#server_settings_content').find('button').show();
            	}
            	else if(server_requirement == "phpini_settings") {
            		$('#phpini_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#phpini_settings_content').find('button').removeAttr('onclick');
            			$('#phpini_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#phpini_settings_content').find('button').html('<i class="fa fa-check"></i>Running');
            		}
            		else {
            			$('#phpini_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
            			$('#phpini_settings_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Missing');
            		}
            		$('#phpini_settings_content').find('button').show();
            	}
            	else if(server_requirement == "php_modules") {
            		$('#php_modules_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#php_modules_content').find('button').removeAttr('onclick');
            			$('#php_modules_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#php_modules_content').find('button').html('<i class="fa fa-check"></i>Running');
            		}
            		else {
            			$('#php_modules_content').find('button').removeClass('btn-success').addClass('btn-danger');
            			$('#php_modules_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Missing');
            		}
            		$('#php_modules_content').find('button').show();
            	}
            	else if(server_requirement == "mysql_settings") {
            		$('#mysql_settings_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#mysql_settings_content').find('button').removeAttr('onclick');
            			$('#mysql_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#mysql_settings_content').find('button').html('<i class="fa fa-check"></i>Running');
            		}
            		else {
            			$('#mysql_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
            			$('#mysql_settings_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Missing');
            		}
            		$('#mysql_settings_content').find('button').show();
            	}
            	else if(server_requirement == "server_ports") {
            		$('#server_ports_content').find('.spinner2').hide();
            		if(response.Action == 1)
            		{
                        $('#server_ports_content').find('button').removeAttr('onclick');
            			$('#server_ports_content').find('button').removeClass('btn-danger').addClass('btn-success');
            			$('#server_ports_content').find('button').html('<i class="fa fa-check"></i>Running');
            		}
            		else {
            			$('#server_ports_content').find('button').removeClass('btn-success').addClass('btn-danger');
            			$('#server_ports_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Missing');
                        $('#server_ports_modal').find('.requirement-list').html(response.server_requirement_html);
            		}

                    $('#requirements_modal').find('.server-ports-content').html(response.all_ports_html);
            		$('#server_ports_content').find('button').show();
            	}
                else if(server_requirement == "cron_jobs_status") {
                    $('#cron_jobs_status_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#cron_jobs_status_content').find('button').removeAttr('onclick');
                        $('#cron_jobs_status_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#cron_jobs_status_content').find('button').html('<i class="fa fa-check"></i>Running');
                    }
                    else {
                        $('#cron_jobs_status_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#cron_jobs_status_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>Failed');
                    }
                    $('#cron_jobs_status_content').find('button').show();
                }
                else if(server_requirement == "mysql_suggestions") {
                    $('#mysql_suggestions_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#mysql_suggestions_content').find('button').removeAttr('onclick');
                        $('#mysql_suggestions_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#mysql_suggestions_content').find('button').html('<i class="fa fa-check"></i>No Suggestions');
                    }
                    else {
                        $('#mysql_suggestions_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#mysql_suggestions_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>View');
                    }
                    $('#mysql_suggestions_content').find('button').show();
                }
                else if(server_requirement == "folder_permissions") {
                    $('#folder_permissions_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#folder_permissions_content').find('button').removeAttr('onclick');
                        $('#folder_permissions_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#folder_permissions_content').find('button').html('<i class="fa fa-check"></i>Correct');
                    }
                    else {
                        $('#folder_permissions_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#folder_permissions_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>View');

                        $('#folder_permissions_modal').find('.requirement-list').html(response.server_requirement_html);
                    }

                    $('#requirements_modal').find('.folder-permissions-content').html(response.server_requirement_html);
                    $('#folder_permissions_content').find('button').show();
                }
                else if(server_requirement == "system_settings") {
                    $('#system_settings_content').find('.spinner2').hide();
                    if(response.Action == 1)
                    {
                        $('#system_settings_content').find('button').removeAttr('onclick');
                        $('#system_settings_content').find('button').removeClass('btn-danger').addClass('btn-success');
                        $('#system_settings_content').find('button').html('<i class="fa fa-check"></i>Correct');
                    }
                    else {
                        $('#system_settings_content').find('button').removeClass('btn-success').addClass('btn-danger');
                        $('#system_settings_content').find('button').html('<i class="fa fa-exclamation-triangle"></i>View');
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