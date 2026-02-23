<?php if($MODULES_OBJ->isEnableServerRequirementValidation() && SITE_TYPE == "Live" && isset($_SESSION['sess_iGroupId']) && $_SESSION['sess_iGroupId'] == 1) { ?>
    <link rel="stylesheet" href="css/requirement.css" />
    
    <div class="server-requirements">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary bg-gray-light" >
                    <div class="panel-heading" >
                        <div class="panel-title-box">
                            <div style="padding: 7px 0">
                                <i class="fa fa-asterisk"></i> Server Requirements
                                <span class="toggle-server-requirements"><i class="fa fa-minus"></i></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-info btn-sm" onclick="openRequirementsModal('requirements_modal')" id="view_server_requirements">View All</button>
                            </div>
                        </div>                                  
                    </div>
                    <div class="blocks">
                        <div id="server_settings_content" class="block-content mb-10">
                            <span>Server Settings</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('server_settings_modal')"></button>
                            </span>
                        </div>
                        <div id="server_ports_content" class="block-content mb-10">
                            <span>Server Ports</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRequirementsModal('server_ports_modal')"></button>
                            </span>
                        </div>
                        <div id="phpini_settings_content" class="block-content mb-10">
                            <span>PHP ini Settings</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('phpini_settings_modal')"></button>
                            </span>
                        </div>
                        <div id="php_modules_content" class="block-content mb-10">
                            <span>PHP Modules</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('php_modules_modal')"></button>
                            </span>
                        </div>
                        <div id="mysql_settings_content" class="block-content mb-10">
                            <span>MySql Settings</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('mysql_settings_modal')"></button>
                            </span>
                        </div>
                        <div id="mysql_suggestions_content" class="block-content mb-10">
                            <span>MySQL Suggestions</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRequirementsModal('mysql_suggestions_modal')"></button>
                            </span>
                        </div>
                        <div id="system_settings_content" class="block-content mb-10">
                            <span>System Settings</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('system_settings_modal')">View</button>
                            </span>
                        </div>
                        <div id="cron_jobs_status_content" class="block-content mb-10">
                            <span>System Cron Jobs</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRequirementsModal('cron_jobs_status_modal')"></button>
                            </span>
                        </div>
                        
                        <div id="folder_permissions_content" class="block-content">
                            <span>Folder Permissions</span>
                            <span>
                                <div class="spinner2"></div>
                                <button type="button" class="btn btn-danger btn-sm" onclick="openRequirementsModal('folder_permissions_modal')"></button>
                            </span>
                        </div>
                        
                        <div id="things_todo_content" class="block-content">
                            <span>Things to do on Server</span>
                            <span>
                                <button type="button" class="btn btn-success btn-sm" onclick="openRequirementsModal('things_todo_modal')" style="display: flex;">View</button>
                            </span>
                        </div>
                    </div>
                    <div class="server-requirements-note">
                        <strong>Note: </strong>Please contact technical team if you have any questions/queries.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr />

<?php include_once('server_requirements.php'); ?>
<script type="text/javascript" src="js/requirement.js"></script>
<?php } ?>