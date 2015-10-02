#!/usr/bin/env sh
#
# install script

prog_dir="$(dirname "$(realpath "${0}")")"
name="$(basename "${prog_dir}")"
tmp_dir="/tmp/DroboApps/${name}"
logfile="${tmp_dir}/install.log"

# boilerplate
if [ ! -d "${tmp_dir}" ]; then mkdir -p "${tmp_dir}"; fi
exec 3>&1 4>&2 1>> "${logfile}" 2>&1
echo "$(date +"%Y-%m-%d %H-%M-%S"):" "${0}" "${@}"
set -o errexit  # exit on uncaught error code
set -o nounset  # exit on unset variable
set -o xtrace   # enable script tracing

# generate cert/key
mkdir -p "${prog_dir}/etc/certs"
if [ ! -f "${prog_dir}/etc/certs/cert.pem" ] || \
   [ ! -f "${prog_dir}/etc/certs/key.pem" ]; then
  "/mnt/DroboFS/Shares/DroboApps/apache/libexec/openssl" req -new -x509 \
    -keyout "${prog_dir}/etc/certs/key.pem" \
    -out "${prog_dir}/etc/certs/cert.pem" \
    -days 3650 -nodes -subj "/C=US/ST=CA/L=San Jose/CN=$(hostname)"
  chmod 640 "${prog_dir}/etc/certs/cert.pem" "${prog_dir}/etc/certs/key.pem"
fi

# copy default configuration files
find "${prog_dir}" -type f -name "*.default" -print | while read deffile; do
  basefile="$(dirname "${deffile}")/$(basename "${deffile}" .default)"
  if [ ! -f "${basefile}" ]; then
    cp -vf "${deffile}" "${basefile}"
  fi
done

# install apache 2.x
/usr/bin/DroboApps.sh install_version apache 2
