package com.map;

import android.graphics.Bitmap;
import android.graphics.Matrix;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;
import com.buddyverse.main.R;
import com.map.models.LatLng;
import com.map.models.MarkerOptions;

public class Marker {
    GoogleMap gMap;
    HuaweiMap hMap;

    Object markerObj;

    MarkerOptions markerOpt;
    Object markerOptions;

    LatLng location;

    float cx = 0;
    float fCx = 0;
    float cy = 0;
    float fCy = 0;

    public Marker() {

    }

    public Marker(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public Marker(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public Marker addMarker(LatLng position, String title) {

        if (gMap != null) {
            markerOptions = new com.google.android.gms.maps.model.MarkerOptions();
            markerObj = gMap.addMarker(((com.google.android.gms.maps.model.MarkerOptions) markerOptions).position(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude)).title(title));
        }
        if (hMap != null) {
            markerOptions = new com.huawei.hms.maps.model.MarkerOptions();
            markerObj = hMap.addMarker(((com.huawei.hms.maps.model.MarkerOptions) markerOptions).position(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude)).title(title));
        }

        location = getPos();

        return this;
    }

    public Marker addMarker(MarkerOptions options) {
        this.markerOpt = options;

        if (options.iconDescriptor == null) {
            options.icon(BitmapDescriptorFactory.fromResource(R.mipmap.ic_dest_marker));
        }

        if (gMap != null) {
            com.google.android.gms.maps.model.BitmapDescriptor iconDescriptor;
            if (options.iconDescriptor.resourceId == -1) {
                iconDescriptor = com.google.android.gms.maps.model.BitmapDescriptorFactory.fromBitmap(options.iconDescriptor.image);
            } else {
                iconDescriptor = com.google.android.gms.maps.model.BitmapDescriptorFactory.fromResource(options.iconDescriptor.resourceId);
            }


            markerOptions = new com.google.android.gms.maps.model.MarkerOptions();

            markerObj = gMap.addMarker(((com.google.android.gms.maps.model.MarkerOptions) markerOptions).position(new com.google.android.gms.maps.model.LatLng(options.position.latitude, options.position.longitude)).icon(iconDescriptor).anchor(options.u, options.v).flat(options.isFlat).title(options.title).snippet(options.snippet));
        }
        if (hMap != null) {
            com.huawei.hms.maps.model.BitmapDescriptor iconDescriptor;
            if (options.iconDescriptor.resourceId == -1) {
                iconDescriptor = com.huawei.hms.maps.model.BitmapDescriptorFactory.fromBitmap(options.iconDescriptor.image);
            } else {
                iconDescriptor = com.huawei.hms.maps.model.BitmapDescriptorFactory.fromResource(options.iconDescriptor.resourceId);
            }

            markerOptions = new com.huawei.hms.maps.model.MarkerOptions();

            markerObj = hMap.addMarker(((com.huawei.hms.maps.model.MarkerOptions) markerOptions).position(new com.huawei.hms.maps.model.LatLng(options.position.latitude, options.position.longitude)).icon(iconDescriptor).anchorMarker(options.u, options.v).flat(options.isFlat).title(options.title).snippet(options.snippet));
        }

        location = getPos();

        return this;
    }


    public float getAnchorU() {

        if (this.markerOpt != null) {
            return this.markerOpt.u;
        }

        return 0.5f;
    }

    public float getAnchorV() {
        if (this.markerOpt != null) {
            return this.markerOpt.v;
        }

        return 0.5f;
    }

    public BitmapDescriptorFactory.BitmapDescriptor getIcon() {
        return this.markerOpt.iconDescriptor;
    }

    private LatLng getPos() {
        LatLng latLng = null;

        if (gMap != null) {
            com.google.android.gms.maps.model.LatLng latLng_tmp = ((com.google.android.gms.maps.model.Marker) markerObj).getPosition();
            latLng = new LatLng(latLng_tmp.latitude, latLng_tmp.longitude);
        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLng latLng_tmp = ((com.huawei.hms.maps.model.Marker) markerObj).getPosition();
            latLng = new LatLng(latLng_tmp.latitude, latLng_tmp.longitude);
        }

        return latLng;
    }

    public Marker setMarkerObj(Object markerObj) {
        this.markerObj = markerObj;

        location = getPos();
        return this;
    }

    public void setIcon(BitmapDescriptorFactory.BitmapDescriptor descriptor) {
        if (this.markerOpt != null) {
            this.markerOpt.icon(descriptor);
        }
        if (gMap != null) {
            com.google.android.gms.maps.model.BitmapDescriptor iconDescriptor;
            if (descriptor.resourceId == -1) {
                iconDescriptor = com.google.android.gms.maps.model.BitmapDescriptorFactory.fromBitmap(descriptor.image);
            } else {
                iconDescriptor = com.google.android.gms.maps.model.BitmapDescriptorFactory.fromResource(descriptor.resourceId);
            }
            ((com.google.android.gms.maps.model.Marker) markerObj).setIcon(iconDescriptor);
        }
        if (hMap != null) {
            com.huawei.hms.maps.model.BitmapDescriptor iconDescriptor;
            if (descriptor.resourceId == -1) {
                iconDescriptor = com.huawei.hms.maps.model.BitmapDescriptorFactory.fromBitmap(descriptor.image);
            } else {
                iconDescriptor = com.huawei.hms.maps.model.BitmapDescriptorFactory.fromResource(descriptor.resourceId);
            }

            ((com.huawei.hms.maps.model.Marker) markerObj).setIcon(iconDescriptor);
        }
    }

    public void flipIcon(float cx, float cy) {


        this.cx = cx != 0 ? cx : this.cx;
        this.cy = cy != 0 ? cy : this.cy;

        cx = this.cx;
        cy = this.cy;
        if (cx == 0) {
            cx = 1;
        }

        if (cy == 0) {
            cy = 1;
        }

        if (this.fCx == cx && this.fCy == cy) {
            return;
        }

        this.fCx = cx;
        this.fCy = cy;

        Bitmap icon = getIcon().image;

        Matrix matrix = new Matrix();
        matrix.postScale(cx, cy, icon.getWidth() / 2f, icon.getHeight() / 2f);
        Bitmap newIcon = Bitmap.createBitmap(icon, 0, 0, icon.getWidth(), icon.getHeight(), matrix, true);
        setIcon(new BitmapDescriptorFactory.BitmapDescriptor(newIcon));
    }

    public void setTitle(String title) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setTitle(title);
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setTitle(title);
        }
    }

    public void remove() {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).remove();
        }
        if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).remove();
        }
    }

    public LatLng getPosition() {
        /*LatLng latLng = null;

        if (gMap != null) {
            com.google.android.gms.maps.model.LatLng latLng_tmp = ((com.google.android.gms.maps.model.Marker) markerObj).getPosition();
            latLng = new LatLng(latLng_tmp.latitude, latLng_tmp.longitude);
        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLng latLng_tmp = ((com.huawei.hms.maps.model.Marker) markerObj).getPosition();
            latLng = new LatLng(latLng_tmp.latitude, latLng_tmp.longitude);
        }*/

        return location;
    }

    public float getRotation() {
        if (gMap != null) {
            return ((com.google.android.gms.maps.model.Marker) markerObj).getRotation();
        } else if (hMap != null) {
            return ((com.huawei.hms.maps.model.Marker) markerObj).getRotation();
        }

        return 0;
    }

    public void setPosition(LatLng position) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setPosition(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setPosition(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
        }

        location = getPos();
    }

    public void setRotation(float rotation) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setRotation(rotation);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setRotation(rotation);
        }
    }

    public void setFlat(boolean isFlat) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setFlat(isFlat);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setFlat(isFlat);
        }
    }

    public void setVisible(boolean isVisible) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setVisible(isVisible);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setVisible(isVisible);
        }
    }

    public String getTitle() {
        if (gMap != null) {
            String title = ((com.google.android.gms.maps.model.Marker) markerObj).getTitle();
            return title == null ? "" : title;
        } else if (hMap != null) {
            String title = ((com.huawei.hms.maps.model.Marker) markerObj).getTitle();
            return title == null ? "" : title;
        }

        return "";
    }

    public void setTag(String tag) {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setTag(tag);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setTag(tag);
        }
    }

    public Object getTag() {
        if (gMap != null) {
            return ((com.google.android.gms.maps.model.Marker) markerObj).getTag();
        } else if (hMap != null) {
            return ((com.huawei.hms.maps.model.Marker) markerObj).getTag();
        }
        return "";
    }

    public String getId() {
        if (gMap != null) {
            return ((com.google.android.gms.maps.model.Marker) markerObj).getId();
        } else if (hMap != null) {
            return ((com.huawei.hms.maps.model.Marker) markerObj).getId();
        }
        return "";
    }

    public void setAnchor(float anchorU, float anchorV) {
        if (this.markerOpt != null) {
            this.markerOpt.u = anchorU;
            this.markerOpt.v = anchorV;
        }

        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).setAnchor(anchorU, anchorV);
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).setMarkerAnchor(anchorU, anchorV);
        }
    }

    public void hideInfoWindow() {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).hideInfoWindow();
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).hideInfoWindow();
        }
    }

    public void showInfoWindow() {
        if (gMap != null) {
            ((com.google.android.gms.maps.model.Marker) markerObj).showInfoWindow();
        } else if (hMap != null) {
            ((com.huawei.hms.maps.model.Marker) markerObj).showInfoWindow();
        }
    }
}
