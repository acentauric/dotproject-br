#
# dotProject BR - upgrade from 2.2.0 (or 2.1.8/2.1.9) to 2.3.0
#
# DO NOT USE THIS SCRIPT DIRECTLY - USE THE INSTALLER OR install/upgrade_cli.php.
#
# 20261024
# 2.3.0 stores project colours as #RRGGBB (HTML colour input).
ALTER TABLE `%dbprefix%projects` MODIFY `project_color_identifier` varchar(7) default '#eeeeee';
UPDATE `%dbprefix%projects` SET `project_color_identifier` = CONCAT('#', `project_color_identifier`) WHERE `project_color_identifier` REGEXP '^[0-9A-Fa-f]{6}$';
