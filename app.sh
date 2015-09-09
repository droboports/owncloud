### OWNCLOUD ###
_build_owncloud() {
local VERSION="8.1.1"
local FOLDER="owncloud"
local FILE="${FOLDER}-${VERSION}.tar.bz2"
local URL="https://download.owncloud.org/community/${FILE}"

_download_bz2 "${FILE}" "${URL}" "${FOLDER}"
mkdir -p "${DEST}/app"
cp -vfaR "target/${FOLDER}/"* "${DEST}/app/"
cp -vfa "target/${FOLDER}/.htaccess" "${DEST}/app/"
}

_build() {
  _build_owncloud
  _package
}
