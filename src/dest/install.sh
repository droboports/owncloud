#!/usr/bin/env sh
#
# install script

prog_dir="$(dirname "$(realpath "${0}")")"
name="$(basename "${prog_dir}")"
data_dir="/mnt/DroboFS/Shares/DroboApps/.AppData/${name}"
tmp_dir="/tmp/DroboApps/${name}"
logfile="${tmp_dir}/install.log"
statusfile="${tmp_dir}/status.txt"
errorfile="${tmp_dir}/error.txt"
incron_dir="/etc/incron.d"

openssl="/mnt/DroboFS/Shares/DroboApps/apache/libexec/openssl"

# boilerplate
if [ ! -d "${tmp_dir}" ]; then mkdir -p "${tmp_dir}"; fi
exec 3>&1 4>&2 1>> "${logfile}" 2>&1
echo "$(date +"%Y-%m-%d %H-%M-%S"):" "${0}" "${@}"
set -o errexit  # exit on uncaught error code
set -o nounset  # exit on unset variable
set -o xtrace   # enable script tracing

# check firmware version
if ! /usr/bin/DroboApps.sh sdk_version &> /dev/null; then
  echo "Unsupported Drobo firmware, please upgrade to the latest version." > "${statusfile}"
  echo "4" > "${errorfile}"
fi

# install apache >= 2.4.18
/usr/bin/DroboApps.sh install_version apache 2.4.18

# copy default configuration files
find "${prog_dir}" -type f -name "*.default" -print | while read deffile; do
  basefile="$(dirname "${deffile}")/$(basename "${deffile}" .default)"
  if [ ! -f "${basefile}" ]; then
    cp -vf "${deffile}" "${basefile}"
  fi
done

if [ -d "${incron_dir}" ] && [ ! -f "${incron_dir}/${name}" ]; then
  cp -vf "${prog_dir}/${name}.incron" "${incron_dir}/${name}"
fi

# migrate data folder to /mnt/DroboFS/Shares/DroboApps/.AppData
if [ ! -d "${data_dir}" ]; then
  mkdir -p "${data_dir}"
fi

# migrate certs
mkdir -p "${data_dir}/certs"
if [ -f "${prog_dir}/etc/certs/cert.pem" ]; then
  mv -f "${prog_dir}/etc/certs/cert.pem" "${data_dir}/certs/cert.pem"
fi
if [ -f "${prog_dir}/etc/certs/key.pem" ]; then
  mv -f "${prog_dir}/etc/certs/key.pem" "${data_dir}/certs/key.pem"
fi

# generate cert/key
if [ ! -f "${data_dir}/certs/cert.pem" ] || \
   [ ! -f "${data_dir}/certs/key.pem" ]; then
  "/mnt/DroboFS/Shares/DroboApps/apache/libexec/openssl" req -new -x509 \
    -keyout "${data_dir}/certs/key.pem" \
    -out "${data_dir}/certs/cert.pem" \
    -days 3650 -nodes -subj "/C=US/ST=CA/L=San Jose/CN=$(hostname)"
  chmod 640 "${data_dir}/certs/cert.pem" "${data_dir}/certs/key.pem"
fi

# migrate data
mkdir -p "${data_dir}/data"
if [ -d "${prog_dir}/app/data" ] && [ ! -h "${prog_dir}/app/data" ]; then
  mv -f "${prog_dir}/app/data/".[!.]* "${data_dir}/data/" || true
  mv -f "${prog_dir}/app/data/"* "${data_dir}/data/" || true
  rmdir "${prog_dir}/app/data"
fi

if [ ! -h "${prog_dir}/app/data" ]; then
  ln -fs "${data_dir}/data" "${prog_dir}/app/data" || true
fi

# upgrade database
if [ -f "${prog_dir}/.updatedb" ]; then
  "${prog_dir}/bin/occ" upgrade && rc=$? || rc=$?
  if [ ${rc} -eq 0 ] || [ ${rc} -eq 3 ]; then
    rm -f "${prog_dir}/.updatedb"
  fi
fi
