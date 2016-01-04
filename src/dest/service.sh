#!/usr/bin/env sh
#
# Service.sh for owncloud

# import DroboApps framework functions
. /etc/service.subr

framework_version="2.1"
name="owncloud"
version="8.2.2"
description="ownCloud is a self-hosted file sync and share server"
depends="apache locale"
webui="WebUI"

prog_dir="$(dirname "$(realpath "${0}")")"
conffile="${prog_dir}/etc/owncloudapp.conf"
apachefile="${DROBOAPPS_DIR}/apache/conf/includes/owncloudapp.conf"
daemon="${DROBOAPPS_DIR}/apache/service.sh"
tmp_dir="/tmp/DroboApps/${name}"
pidfile="${tmp_dir}/pid.txt"
logfile="${tmp_dir}/log.txt"
statusfile="${tmp_dir}/status.txt"
errorfile="${tmp_dir}/error.txt"

shares_conf="/mnt/DroboFS/System/DNAS/configs/shares.conf"
shares_dir="/mnt/DroboFS/Shares"

# check firmware version
_firmware_check() {
  local rc
  local semver
  rm -f "${statusfile}" "${errorfile}"
  if [ -z "${FRAMEWORK_VERSION:-}" ]; then
    echo "Unsupported Drobo firmware, please upgrade to the latest version." > "${statusfile}"
    echo "4" > "${errorfile}"
    return 1
  fi
  semver="$(/usr/bin/semver.sh "${framework_version}" "${FRAMEWORK_VERSION}")"
  if [ "${semver}" == "1" ]; then
    echo "Unsupported Drobo firmware, please upgrade to the latest version." > "${statusfile}"
    echo "4" > "${errorfile}"
    return 1
  fi
  return 0
}

# return the data directory from config.php
_get_data_dir() {
  local datadir=""
  if [ -s "${prog_dir}/app/config/config.php" ]; then
    datadir="$(awk -F\' '$2 == "datadirectory" {print $4}' "${prog_dir}/app/config/config.php")"
  fi
  if [ -z "${datadir}" ]; then
    datadir="${prog_dir}/app/data"
  fi
  echo "${datadir}"
}

# All shares will be exposed to the admin group.
_load_shares() {
  local data_dir
  local mountfile
  local share_count
  local share_name
  local share_inode

  data_dir="$(_get_data_dir)"
  mountfile="${data_dir}/mount.json"

  # perform changes on a temporary file
  if [ ! -f "${mountfile}" ] || [ "$(cat "${mountfile}")" = "[]" ]; then
    echo '{ "group": { "admin": { } } }' > "${mountfile}.tmp"
  else
    cp "${mountfile}" "${mountfile}.tmp"
  fi

  # remove all existing shares
  "${prog_dir}/libexec/jq" '.group.admin |= with_entries(select(.value.options.datadir | startswith("/mnt/DroboFS/Shares/") | not)) | .' "${mountfile}.tmp" > "${mountfile}.tmp.new" || true
  if [ -f "${mountfile}.tmp.new" ]; then
    mv "${mountfile}.tmp.new" "${mountfile}.tmp"
  fi

  # add new shares
  share_count=$("${prog_dir}/libexec/xmllint" --xpath "count(//Share)" "${shares_conf}")
  if [ ${share_count} -eq 0 ]; then
    echo "No shares found."
  else
    echo "Found ${share_count} shares."
    for i in $(seq 1 ${share_count}); do
      share_name=$("${prog_dir}/libexec/xmllint" --xpath "//Share[${i}]/ShareName/text()" "${shares_conf}")
      share_inode=$(stat -c %i "/mnt/DroboFS/Shares/${share_name}")
      "${prog_dir}/libexec/jq" '. * { "group": { "admin": {"/$user/files/'"${share_name}"'": { "id": '"${share_inode}"', "backend": "local", "authMechanism": "null::null", "options": { "datadir": "/mnt/DroboFS/Shares/'"${share_name}"'" }, "priority": 150, "mountOptions": { "previews": true, "filesystem_check_changes": 2 } }}}}' "${mountfile}.tmp" > "${mountfile}.tmp.new"
      mv "${mountfile}.tmp.new" "${mountfile}.tmp"
    done
  fi

  if ! diff -q "${mountfile}.tmp" "${mountfile}"; then
    mv "${mountfile}.tmp" "${mountfile}"
  else
    rm -f "${mountfile}.tmp"
  fi
}

start() {
  local rc
  _firmware_check
  # upgrade database
  if [ -f "${prog_dir}/.updatedb" ]; then
    "${prog_dir}/bin/occ" upgrade && rc=$? || rc=$?
    if [ ${rc} -eq 0 ] || [ ${rc} -eq 3 ]; then
      rm -f "${prog_dir}/.updatedb"
    fi
  fi
  # ensure files_external is enabled
  "${prog_dir}/bin/occ" app:enable files_external || true
  reload
  cp -vf "${conffile}" "${apachefile}"
  "${daemon}" restart || true
  return 0
}

is_running() {
  if [ -e "${apachefile}" ]; then
    return 0
  fi
  return 1
}

stop() {
  rm -vf "${apachefile}"
  "${daemon}" restart || true
  return 0
}

force_stop() {
  stop
}

reload() {
  _load_shares
}

# boilerplate
if [ ! -d "${tmp_dir}" ]; then mkdir -p "${tmp_dir}"; fi
exec 3>&1 4>&2 1>> "${logfile}" 2>&1
STDOUT=">&3"
STDERR=">&4"
echo "$(date +"%Y-%m-%d %H-%M-%S"):" "${0}" "${@}"
set -o errexit  # exit on uncaught error code
set -o nounset  # exit on unset variable
set -o xtrace   # enable script tracing

main "${@}"
