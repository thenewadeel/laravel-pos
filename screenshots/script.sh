crop_mob="420x903+45+120"
crop_led="1885x945+1915+82"
crop_pad="1015x820+860+147"
for f in *.png ; do magick "$f" -crop "$crop_mob" "${f:0:-4} - mob.jpg" &&
                    magick "$f" -crop "$crop_pad" "${f:0:-4} - pad.jpg" &&
                    magick "$f" -crop "$crop_led" "${f:0:-4} - led.jpg"; done