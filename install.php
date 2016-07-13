<?php

/**
 * Copyright (c) 2009, Skalfa LLC
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$dbPrefix = OW_DB_PREFIX;

$dbo = OW::getDbo();
$logger = OW::getLogger();

// add absent columns (fix for https://github.com/skalfa/workflow/issues/1223)
try
{
    $query = "SHOW COLUMNS FROM `{$dbPrefix}base_geolocation_ip_to_country` LIKE 'ipFrom'";
    $column = $dbo->queryForRow($query);

    if ( !$column )
    {
        $query = "ALTER TABLE `{$dbPrefix}base_geolocation_ip_to_country` ADD `ipFrom` bigint UNSIGNED";
        $dbo->query($query);
    }
    else
    {
        $query = "ALTER TABLE `{$dbPrefix}base_geolocation_ip_to_country` CHANGE `ipFrom` `ipFrom` bigint UNSIGNED";
        $dbo->query($query);
    }
}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

try
{
    $query = "SHOW COLUMNS FROM `{$tblPrefix}base_geolocation_ip_to_country` LIKE 'ipTo'";
    $column = $dbo->queryForRow($query);

    if ( !$column )
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` ADD `ipTo` bigint UNSIGNED";
        $dbo->query($query);
    }
    else
    {
        $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` CHANGE `ipTo` `ipTo` bigint UNSIGNED";
        $dbo->query($query);
    }
}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

// add index
try
{
    $query = "ALTER TABLE `{$tblPrefix}base_geolocation_ip_to_country` ADD INDEX `ipRange` (`ipFrom`, `ipTo`)";
    $dbo->query($query);

}
catch (Exception $e)
{
    $logger->addEntry(json_encode($e));
}

$plugin = OW::getPluginManager()->getPlugin('geolocationdata');
OW::getLanguage()->importPluginLangs($plugin->getRootDir() . 'langs.zip', 'geolocationdata');
$sqlFile = OW::getPluginManager()->getPlugin( 'geolocationdata' )->getRootDir()."ow_geolocationdata_ipv4.sql";

 if ( !($fd = @fopen($sqlFile, 'rb')) ) {
            throw new LogicException('SQL dump file `'.$sqlFile.'` not found');
        }

 $lineNo = 0;
        $query = '';
        while ( false !== ($line = fgets($fd, 10240)) )
        {
            $lineNo++;

            if ( !strlen(($line = trim($line)))
                || $line{0} == '#' || $line{0} == '-'
                || preg_match('~^/\*\!.+\*/;$~siu', $line) ) {
                continue;
            }

            $query .= $line;

            if ( $line{strlen($line)-1} != ';' ) {
                continue;
            }

            $query = str_replace('%%TBL-PREFIX%%', OW_DB_PREFIX, $query);

            try {
                OW::getDbo()->query($query);
            }
            catch ( Exception $e ) {
                throw new LogicException('<b>ow_includes/config.php</b> file is incorrect. Update it with details provided below.');
            }

            $query = '';
        }

        fclose($fd);

