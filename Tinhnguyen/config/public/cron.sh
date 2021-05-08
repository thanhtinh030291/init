SHELL=/bin/sh
PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin

ping -q -c5 192.168.148.3 > /dev/null
if [ $? -eq 0 ]
then
    echo ""
else
    echo "failed"
    #https://github.com/adrienverge/openfortivpn
    /usr/local/bin/openfortivpn -c /etc/openfortivpn/hbs &
    sleep 30
fi

dir=$(dirname "$0")
/bin/php72 $dir/cron.php >> /var/log/httpd/cc_cron.log 2>&1