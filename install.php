<?php

$dbPrefix = OW_DB_PREFIX;
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

