<?php

if (class_exists('AdminPanelAction')){

    class admin_plugin_entrycover extends AdminPanelAction {
        var $langres = 'plugin:entrycover';

        function setup() {
            
        }

        function main() {
            $pluginOptions = plugin_getoptions(PLUGIN_NAME);
            $this->smarty->assign('allowTag', isset($pluginOptions['allowTag']) && $pluginOptions['allowTag']);
            $this->smarty->assign('admin_resource', "plugin:entrycover/admin.plugin.entrycover");
        }

        function onsubmit($data = null) {
            if(isset($_POST)) {
                plugin_addoption(PLUGIN_NAME, 'allowTag', isset($_POST['entryCoverTagCheckBox']));
                plugin_saveoptions(PLUGIN_NAME);
				$this->smarty->assign('success', 1);
            }
            $this->smarty->assign('allowTag', isset($_POST['entryCoverTagCheckBox']) && $_POST['entryCoverTagCheckBox']);
            $this->smarty->assign('admin_resource', "plugin:entrycover/admin.plugin.entrycover");
		}
    }

    admin_addpanelaction('plugin', 'entrycover', true);

}
?>