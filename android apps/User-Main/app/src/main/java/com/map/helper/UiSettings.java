package com.map.helper;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;

public class UiSettings {
    GoogleMap gMap;
    HuaweiMap hMap;

    public UiSettings() {
    }

    public UiSettings(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public UiSettings(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public void setCompassEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setCompassEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setCompassEnabled(isEnabled);
        }
    }

    public void setZoomGesturesEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setZoomGesturesEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setZoomGesturesEnabled(isEnabled);
        }
    }

    public void setMapToolbarEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setMapToolbarEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setMapToolbarEnabled(isEnabled);
        }
    }

    public void setTiltGesturesEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setTiltGesturesEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setTiltGesturesEnabled(isEnabled);
        }
    }

    public void setMyLocationButtonEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setMyLocationButtonEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setMyLocationButtonEnabled(isEnabled);
        }
    }

    public void setAllGesturesEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setAllGesturesEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setAllGesturesEnabled(isEnabled);
        }
    }

    public void setZoomControlsEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setZoomControlsEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setZoomControlsEnabled(isEnabled);
        }
    }

    public void setScrollGesturesEnabled(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setScrollGesturesEnabled(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setScrollGesturesEnabled(isEnabled);
        }
    }

    public void setScrollGesturesEnabledDuringRotateOrZoom(boolean isEnabled) {
        if (gMap != null) {
            gMap.getUiSettings().setScrollGesturesEnabledDuringRotateOrZoom(isEnabled);
        } else if (hMap != null) {
            hMap.getUiSettings().setScrollGesturesEnabledDuringRotateOrZoom(isEnabled);
        }
    }
}
