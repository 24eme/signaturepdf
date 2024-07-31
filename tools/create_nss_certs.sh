#!/bin/bash

nss_dir=$1
nss_pass=$2
nss_nick=$3
signaturepdf_url=$4

if ! test "$signaturepdf_url"; then
    echo "Usage:"
    echo "\t$0 <nss_dir> <nss_pass> <nss_nick> <signaturepdf_url>";
    exit 1:
fi

if ! test -d "$nss_dir"; then
    echo "ERROR: nss_dir \"$nss_dir\" should exist";
    exit 2;
fi
if echo "$nss_nick" | grep '\.' > /dev/null ; then
    echo "ERROR: $nss_nick should not contain . ";
    exit 3;
fi

signaturepdf_domain=$(echo $signaturepdf_url | sed 's/https*:\/\///' | sed 's/\/.*//')
signaturepdf_path=$(echo $signaturepdf_url | sed 's/https*:\/\///' | sed 's/.*'$signaturepdf_domain'\/*//')
signaturepdf_dc=$(echo $signaturepdf_domain | tr '.' '\n' | sed 's/^/DC=/' | tr '\n' ',' | sed 's/,$//')
if test "$signaturepdf_path"; then
    signaturepdf_dc="DC=/"$signaturepdf_path','$signaturepdf_dc;
fi

echo "$nss_pass" > /tmp/nss.$$.tmp
certutil -N -f /tmp/nss.$$.tmp -d "$nss_dir"

echo $RANDOM" CACert "$(date)" $$ "$RANDOM | shasum > /tmp/nss_noise.$$.tmp
certutil -S -s "CN=PDF Sign CA Cert,$signaturepdf_dc" -n "PDFSignCA" -z /tmp/nss_noise.$$.tmp -x -t "CT,CT,CT" -v 120 -m 1234 -d "$nss_dir" -f /tmp/nss.$$.tmp

echo $RANDOM" $signaturepdf_dc "$(date)" $$ "$RANDOM | shasum >> /tmp/nss_noise.$$.tmp
certutil -S -s "CN=$nss_nick,$signaturepdf_dc" -n "$nss_nick" -c "PDFSignCA" -z /tmp/nss_noise.$$.tmp -t ",," -m 730  -d "$nss_dir" -f /tmp/nss.$$.tmp

echo "Certs created :"
echo "==============="
certutil -f /tmp/nss.$$.tmp -d $nss_dir -L
echo "Private keys created :"
echo "======================"
certutil -f /tmp/nss.$$.tmp -d $nss_dir -K

rm /tmp/nss.$$.tmp /tmp/nss_noise.$$.tmp
