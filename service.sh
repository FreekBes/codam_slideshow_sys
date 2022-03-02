#!/usr/bin/env bash

# Set script to exit immediately if a command exits with a non-zero status
set -e

# Displays images in a loop. If image starts with number + underscore (10_ for example),
# the image is displayed for number amount of seconds.

# This script's directory
BASEDIR="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"

# Path to default programme directory
PATH_TO_DEFAULT="${BASEDIR}/programmes/default"

# Default image if no programme at all
DEFAULT_IMG="${BASEDIR}/0_10_default.jpeg"

# Default interval if no interval is specified
DEFAULT_INTERVAL="10"

# This is used for not resending image when the same image is already on the screen
DISPLAYED=""

# This is used for checking if programme folders are all empty
NOTHING="1"

display_media () {
	FILE_NAME="${1##*/}"

	# Skip over *.extension files, we only want absolute specific paths
	if [ "${FILE_NAME}" = "*.jpg" ] || [ "${FILE_NAME}" = "*.jpeg" ] || [ "${FILE_NAME}" = "*.png" ] || [ "${FILE_NAME}" = "*.mp4" ]; then
		return 0
	fi

	# Skip over files that seem to be misconfigured
	if [[ ! "${FILE_NAME}" =~ ^[0-9]+_[0-9]+_[A-Za-z0-9\-]*\.(jpg|jpeg|png|mp4) ]]; then
		echo "Invalid file ${FILE_NAME}"
		return 1
	fi

	# Parse the duration the media should be displayed
	DURATION="$(echo "${FILE_NAME}" | sed -e 's/.*_\(.*\)_.*/\1/')"

	# Convert duration from milliseconds to seconds
	DURATION=$(awk "BEGIN { print ${DURATION}/1000 }")

	# Check if media is already being displayed, if not, display it
	if [ ! "${1}" = "${DISPLAYED}" ]; then
		# Detect if video
		if [[ "${FILE_NAME}" =~ \.mp4 ]]; then
			ORIG_DURATION="$(echo "${FILE_NAME}" | sed -e 's/.*\-\(.*\)\..*/\1/')"

			echo "Showing video..."
			xbmc-send -a "Action(Stop)"
			xbmc-send -a "PlayMedia(${1})"
			# Cannot use RepeatOne, as that crashes Kodi when the media isn't loaded yet
			# Could sleep for a while, but that sometimes still results in crashing.
			# xbmc-send -a "PlayerControl(RepeatOne)"
		else
			echo "Showing image..."
			xbmc-send -a "ShowPicture(${1})"
		fi
	fi

	echo "Sleeping for ${DURATION} seconds..."
	sleep "${DURATION}"
	DISPLAYED="${1}"
	NOTHING="0"
}

# Infinite loop over files in both the default programme folder and today's
while true; do
	PATH_TO_TODAY="${BASEDIR}/programmes/$(date +%F)"
	NOTHING="1"

	# Loop over all media in default programme
	# Only do so if no programme for today exists, or if today's programme exists AND
	# in the folder of said programme there's a file called ".default_enabled"
	if [ ! -d "${PATH_TO_TODAY}" ] || ([ -d "${PATH_TO_TODAY}" ] && [ -f "${PATH_TO_TODAY}/.default_enabled" ]); then
		if [ -d "${PATH_TO_DEFAULT}" ]; then
			echo "Running default programme..."
			for IMAGE in ${PATH_TO_DEFAULT}/*.{jpg,jpeg,png,mp4}; do
				display_media "$IMAGE"
			done
		fi
	fi

	# Loop over all media in today's programme, if it exists
	if [ -d "${PATH_TO_TODAY}" ]; then
		echo "Running today's programme..."
		for IMAGE in ${PATH_TO_TODAY}/*.{jpg,jpeg,png,mp4}; do
			display_media "$IMAGE"
		done
	fi

	# If nothing was displayed by a programme, display the default image
	if [ "{$NOTHING}" = "1" ]; then
		echo "Showing default image..."
		display_media "$DEFAULT_IMAGE"
	fi
done
