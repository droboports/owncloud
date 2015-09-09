#!/usr/bin/env sh
#
# Service.sh for owncloud

# import DroboApps framework functions
. /etc/service.subr

framework_version="2.1"
name="owncloud"
version="8.1.1"
description="ownCloud is a self-hosted file sync and share server"
depends="apache"
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

# backwards compatibility
if [ -z "${FRAMEWORK_VERSION:-}" ]; then
  framework_version="2.0"
  . "${prog_dir}/libexec/service.subr"
fi

start() {
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
