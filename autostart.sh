# Disable any form of screen saver / screen blanking / power management
xset s off
xset s noblank
xset -dpms

# Allow quitting X server with CTRL-ALT-Backspace
setxkbmap -option terminate:ctrl_alt_bksp

# Start Chromium in kiosk mode, make sure to make it think it exited cleanly last time
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/' ~/.config/chromium/'Local State'
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/; s/"exit_type":"[^"]\+"/"exit_type":"Normal"/' ~/.config/chromium/Default/Preferences
chromium-browser --disable-infobars --kiosk 'http://localhost/show.php?day=today&num=0'