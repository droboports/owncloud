### OWNCLOUD ###
_build_owncloud() {
local VERSION="8.2.0"
local FOLDER="owncloud"
local FILE="${FOLDER}-${VERSION}.tar.bz2"
local URL="https://download.owncloud.org/community/${FILE}"

_download_bz2 "${FILE}" "${URL}" "${FOLDER}"

cp -vf "src/${FOLDER}-${VERSION}-disable-trusted-domains.patch" "target/${FOLDER}/"
cp -vf "src/${FOLDER}-${VERSION}-enable-files_external.patch" "target/${FOLDER}/"
pushd "target/${FOLDER}/"
patch -p1 -i "${FOLDER}-${VERSION}-disable-trusted-domains.patch"
patch -p1 -i "${FOLDER}-${VERSION}-enable-files_external.patch"
rm "${FOLDER}-${VERSION}-disable-trusted-domains.patch"
rm "${FOLDER}-${VERSION}-enable-files_external.patch"
popd

mkdir -p "${DEST}/app"
cp -vfaR "target/${FOLDER}/"* "${DEST}/app/"
cp -vfa "target/${FOLDER}/.htaccess" "${DEST}/app/"
}

_build() {
  _build_owncloud
  _package
}
