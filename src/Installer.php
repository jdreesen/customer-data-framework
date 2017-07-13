<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 15.11.2016
 * Time: 14:06
 */

namespace CustomerManagementFrameworkBundle;

use Pimcore\Extension\Bundle\Installer\AbstractInstaller;
use Pimcore\Logger;

class Installer extends AbstractInstaller {

    public function install() {

        $this->installPermissions();
        $this->installDatabaseTables();
        $this->installClasses();
        $this->installConfig();


        return true;
    }

    public function isInstalled()
    {
        // implement your own logic here
        return file_exists(PIMCORE_CONFIGURATION_DIRECTORY . '/plugins/CustomerManagementFramework/config.php');
    }

    public function canBeInstalled()
    {
        return !$this->isInstalled();
    }

    /**
     * {@inheritdoc}
     */
    public function needsReloadAfterInstall()
    {
        return true;
    }

    public function installPermissions() {

        $permissions = [
            "plugin_customermanagementframework_activityview",
            "plugin_customermanagementframework_customerview"
        ];

        foreach($permissions as $key) {
            $permission = new \Pimcore\Model\User\Permission\Definition();
            $permission->setKey($key);

            $res = new \Pimcore\Model\User\Permission\Definition\Dao();
            $res->configure();
            $res->setModel($permission);
            $res->save();
        }
    }

    public function installDatabaseTables() {
        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_activities` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `customerId` int(11) unsigned NOT NULL,
              `activityDate` bigint(20) unsigned DEFAULT NULL,
              `type` varchar(255) NOT NULL,
              `implementationClass` varchar(255) NOT NULL,
              `o_id` int(11) unsigned DEFAULT NULL,
              `a_id` varchar(255) DEFAULT NULL,
              `attributes` blob,
              `md5` char(32) DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `customerId` (`customerId`),
              KEY `o_id` (`o_id`),
              KEY `a_id` (`a_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_deletions` (
              `id` int(11) unsigned NOT NULL,
              `entityType` char(20) NOT NULL,
              `type` varchar(255) NOT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              KEY `type` (`entityType`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_segmentbuilder_changes_queue` (
              `customerId` int(11) unsigned NOT NULL,
              UNIQUE KEY `customerId` (`customerId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_actions` (
              `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
              `ruleId` int(20) unsigned NOT NULL,
              `actionDelay` int(20) unsigned NOT NULL,
              `implementationClass` varchar(255) NOT NULL,
              `options` text,
              `creationDate` bigint(20) NOT NULL,
              `modificationDate` bigint(20) NOT NULL,
              PRIMARY KEY (`id`),
              KEY `ruleId` (`ruleId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_rules` (
              `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(50) DEFAULT NULL,
              `description` text,
              `active` tinyint(1) unsigned DEFAULT NULL,
              `trigger` text COMMENT 'configuration of triggers',
              `condition` text COMMENT 'configuration of conditions',
              `creationDate` int(11) NOT NULL,
              `modificationDate` int(11) NOT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `name` (`name`),
              KEY `active` (`active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_actiontrigger_queue` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `customerId` int(11) unsigned NOT NULL,
              `actionDate` bigint(20) unsigned DEFAULT NULL,
              `actionId` int(11) unsigned DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `customerId` (`customerId`),
              KEY `actionId` (`actionId`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_sequence_numbers` (
              `name` char(50) NOT NULL,
              `number` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicatesindex` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `duplicateData` text NOT NULL,
              `duplicateDataMd5` varchar(32) DEFAULT NULL,
              `fieldCombination` char(255) NOT NULL DEFAULT '',
              `fieldCombinationCrc` int(11) unsigned NOT NULL,
              `metaphone` varchar(50) DEFAULT NULL,
              `soundex` varchar(50) DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `duplicateDataCrc` (`duplicateDataMd5`),
              KEY `fieldCombination` (`fieldCombination`),
              KEY `soundex` (`soundex`),
              KEY `metaphone` (`metaphone`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicatesindex_customers` (
              `duplicate_id` int(11) unsigned NOT NULL,
              `customer_id` int(11) unsigned NOT NULL,
              KEY `duplicate_id` (`duplicate_id`),
              KEY `customer_id` (`customer_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_duplicates_false_positives` (
              `row1` text NOT NULL,
              `row2` text NOT NULL,
              `row1Details` text NOT NULL,
              `row2Details` text NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        \Pimcore\Db::get()->query(
            "CREATE TABLE IF NOT EXISTS `plugin_cmf_potential_duplicates` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `duplicateCustomerIds` varchar(255) NOT NULL DEFAULT '',
              `fieldCombinations` text NOT NULL,
              `declined` tinyint(1) DEFAULT NULL,
              `modificationDate` bigint(20) unsigned DEFAULT NULL,
              `creationDate` bigint(20) unsigned DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `duplicateIds` (`duplicateCustomerIds`),
              KEY `declined` (`declined`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );
    }

    public static function installClasses()
    {
        $sourcePath = __DIR__ . '/../install/class_source';

        self::installClass("CustomerSegmentGroup", $sourcePath . '/class_CustomerSegmentGroup_export.json');
        self::installClass("CustomerSegment", $sourcePath . '/class_CustomerSegment_export.json');
        self::installClass("ActivityDefinition", $sourcePath . '/class_ActivityDefinition_export.json');
        self::installClass("SsoIdentity", $sourcePath . '/class_SsoIdentity_export.json');
        self::installClass("TermSegmentBuilderDefinition", $sourcePath . '/class_TermSegmentBuilderDefinition_export.json');
    }

    public static function installClass($classname, $filepath) {
        $class = \Pimcore\Model\Object\ClassDefinition::getByName($classname);
        if(!$class) {
            $class = new \Pimcore\Model\Object\ClassDefinition();
            $class->setName($classname);
            $class->setGroup("CustomerManagement");
        }
        $json = file_get_contents($filepath);

        $success = \Pimcore\Model\Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json);
        if(!$success){
            Logger::err("Could not import $classname Class.");
        }
    }

    public static function installConfig() {

        $dir = PIMCORE_CONFIGURATION_DIRECTORY . '/plugins/CustomerManagementFramework';

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        foreach(["config.php"] as $file) {

            $target = PIMCORE_CONFIGURATION_DIRECTORY . '/plugins/CustomerManagementFramework/' . $file;

            if (!is_file($target)) {

                copy(
                    realpath(__DIR__ . "/../install/config/") . '/' . $file,
                    $target);
            }
        }
    }
}