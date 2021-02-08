<?php

namespace Kitzberger\BoseSoundtouch;

class Discoverer
{
    const SERVICE_TYPE = '_soundtouch._tcp';
    const DOMAIN = 'local';

    protected static $devicesCache = null;

    public static $debug = false;

    /**
     * Returns the IPs and names of a certain service type and a certain domain
     *
     * @param  string $serviceType
     * @param  string $domain
     * @return array
     */
    static public function getServiceDevices($serviceType = self::SERVICE_TYPE, $domain = self::DOMAIN)
    {
        $tmpFile = self::getCacheFileName($serviceType, $domain);

        self::log('tmp file: ' . $tmpFile);

        if (0 && file_exists($tmpFile)) {
            self::log('cache file is existing, reading files content now');
            self::$devicesCache = json_decode(file_get_contents($tmpFile));
        } else {
            self::log('cache file is not existing, trying to discover devices now');
            self::$devicesCache = self::discoverServiceDevices($serviceType, $domain);
            file_put_contents($tmpFile, json_encode(self::$devicesCache));
        }

        return self::$devicesCache;
    }

    static public function clearCache($serviceType = self::SERVICE_TYPE, $domain = self::DOMAIN)
    {
        $tmpFile = self::getCacheFileName($serviceType . $domain);
        if (file_exists($tmp)) {
            unlink($tmp);
        }
    }

    static protected function getCacheFileName($key)
    {
        return sys_get_temp_dir() . '/soundtouch-client-' . md5($key);
    }


    static protected function discoverServiceDevices($serviceType, $domain)
    {
        $results = [];
        $command = 'avahi-browse ' . $serviceType . ' --domain ' . $domain . ' --resolve --terminate --parsable';
        self::log('command: ' . $command);
        exec($command, $results);
        self::log('ouput: ');
        self::log($results);

        // Array
        // (
        //      [0] => +;enp2s0;IPv4;Kitchen;_soundtouch._tcp;local
        //      [1] => +;enp2s0;IPv4;Living room;_soundtouch._tcp;local
        //      [2] => =;enp2s0;IPv4;Kitchen;_soundtouch._tcp;local;rhino-12345678.local;192.168.178.22;8090;"MODEL=SoundTouch" "MANUFACTURER=Bose Corporation" "MAC=123454543" "DESCRIPTION=SoundTouch"
        //      [3] => =;enp2s0;IPv4;Living room;_soundtouch._tcp;local;rhino-12346463.local;192.168.178.42;8090;"MODEL=SoundTouch" "MANUFACTURER=Bose Corporation" "MAC=21521521" "DESCRIPTION=SoundTouch"
        // )

        $ips = [];
        foreach ($results as $result) {
            $result = explode(';', $result);
            if ($result[0] === '=') {
                if (filter_var($result[7], FILTER_VALIDATE_IP))
                $ips[] = [
                    'ip' => $result[7],
                    'name' => str_replace(
                        [
                            '\195\188'
                        ],
                        [
                            'ü'
                        ],
                        $result[3]
                    ),
                ];
            }
        }

        return $ips;
    }

    static protected function log($data)
    {
        if (self::$debug) {
            if (is_string($data)) {
                echo $data .  '<br>';
            } elseif (is_array($data)) {
                echo '<pre>';
                print_r($data);
                echo '</pre>';
            }
        }
    }
}
