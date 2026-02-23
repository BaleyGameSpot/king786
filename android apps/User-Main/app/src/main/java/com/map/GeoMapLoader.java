package com.map;

import android.os.Handler;
import android.os.Looper;
import android.view.View;
import android.view.ViewTreeObserver;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;

import com.general.files.MyApp;
import com.google.android.gms.maps.GoogleMap;
import com.google.android.gms.maps.MapsInitializer;
import com.google.android.gms.maps.OnMapReadyCallback;
import com.google.android.gms.maps.SupportMapFragment;
import com.google.android.gms.maps.model.MapStyleOptions;
import com.huawei.hms.maps.HuaweiMap;
import com.buddyverse.main.R;
import com.map.helper.UiSettings;
import com.map.minterface.CancelableCallback;
import com.map.minterface.OnCameraChangeListener;
import com.map.minterface.OnCameraIdleListener;
import com.map.minterface.OnCameraMoveCanceledListener;
import com.map.minterface.OnCameraMoveListener;
import com.map.minterface.OnCameraMoveStartedListener;
import com.map.minterface.OnMapClickListener;
import com.map.minterface.OnMarkerClickListener;
import com.map.minterface.setOnMapLoadedCallback;
import com.map.models.CircleOptions;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.map.models.MarkerOptions;
import com.map.models.PolylineOptions;
import com.map.models.RoundCap;
import com.utils.Logger;

@SuppressWarnings("ALL")
public class GeoMapLoader implements OnMapReadyCallback, com.huawei.hms.maps.OnMapReadyCallback {

    private AppCompatActivity mActInstance;
    private int fragmentId;

    private OnMapReadyCallback mCallbacks;
    private Fragment supportFragment;

    public GeoMapLoader(AppCompatActivity mActInstance, int fragmentId) {
        this.mActInstance = mActInstance;
        this.fragmentId = fragmentId;
    }

    public void bindMap(OnMapReadyCallback mCallbacks) {

        if (mCallbacks == null) {
            throw new AssertionError("mCallbacks should not be null");
        }

        this.mCallbacks = mCallbacks;

        if (MyApp.getInstance().isHMSOnly()) {

            try {
                com.huawei.hms.maps.MapsInitializer.initialize(MyApp.getInstance().getApplicationContext());
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

            com.huawei.hms.maps.SupportMapFragment supportMapFragment = com.huawei.hms.maps.SupportMapFragment.newInstance();

            supportFragment = supportMapFragment;

            mActInstance.getSupportFragmentManager().beginTransaction().setReorderingAllowed(true).add(fragmentId, supportMapFragment, null).commit();

            supportMapFragment.getMapAsync(this);

            new Handler(Looper.myLooper()).postDelayed(new Runnable() {
                @Override
                public void run() {
                    supportMapFragment.onResume();
                }
            }, 5000);


        } else {

            try {
                MapsInitializer.initialize(mActInstance);
            } catch (Exception e) {
                Logger.e("Exception", "::" + e.getMessage());
            }

            SupportMapFragment supportMapFragment = SupportMapFragment.newInstance();

            supportFragment = supportMapFragment;

            mActInstance.getSupportFragmentManager().beginTransaction().setReorderingAllowed(true).add(fragmentId, supportMapFragment, null).commit();

            supportMapFragment.getMapAsync(this);

        }
    }

    @Override
    public void onMapReady(@NonNull GoogleMap googleMap) {
        googleMap.setMapStyle(MapStyleOptions.loadRawResourceStyle(mActInstance, R.raw.map_style));
        this.mCallbacks.onMapReady(new GeoMap(googleMap, supportFragment));
    }

    @Override
    public void onMapReady(HuaweiMap huaweiMap) {
        this.mCallbacks.onMapReady(new GeoMap(huaweiMap, supportFragment));
    }

    public interface OnMapReadyCallback {
        void onMapReady(GeoMap geoMap);
    }

    @SuppressWarnings("deprecation")
    public class GeoMap {
        GoogleMap googleMap;
        HuaweiMap huaweiMap;
        UiSettings uiSettings;
        CameraUpdate cameraUpdate;
        Fragment supportFragment;

        public GeoMap(GoogleMap googleMap, Fragment supportFragment) {
            this.googleMap = googleMap;
            this.supportFragment = supportFragment;
            buildMapObjects();
        }

        public GeoMap(HuaweiMap huaweiMap, Fragment supportFragment) {
            this.huaweiMap = huaweiMap;
            this.supportFragment = supportFragment;
            buildMapObjects();
        }

        private void buildMapObjects() {

            if (googleMap != null) {
                uiSettings = new UiSettings(googleMap);
                cameraUpdate = new CameraUpdate(googleMap);

            } else if (huaweiMap != null) {
                uiSettings = new UiSettings(huaweiMap);
                cameraUpdate = new CameraUpdate(huaweiMap);
            } else {
                uiSettings = new UiSettings();
                cameraUpdate = new CameraUpdate();
            }

            getUiSettings().setZoomControlsEnabled(false);

        }

        public UiSettings getUiSettings() {
            return uiSettings;
        }

        public void moveCamera(LatLng latLng) {
            cameraUpdate.moveCamera(latLng);
        }

        public void moveCamera(@NonNull LatLngBounds bounds, int padding) {
            cameraUpdate.moveCamera(bounds, padding);
        }

        public void moveCamera(@NonNull LatLngBounds bounds, int width, int height, int padding) {
            cameraUpdate.moveCamera(bounds, width, height, padding);
        }

        public void animateCamera(LatLng latLng) {
            cameraUpdate.animateCamera(latLng);
        }

        public void animateCamera(@NonNull LatLngBounds bounds, int width, int height, int padding) {
            cameraUpdate.animateCamera(bounds, width, height, padding);
        }

        public void animateCamera(@NonNull LatLngBounds bounds, int width, int height, int padding, CancelableCallback callback) {
            cameraUpdate.animateCamera(bounds, width, height, padding, callback);
        }

        public void animateCamera(@NonNull LatLngBounds bounds, int padding) {
            cameraUpdate.animateCamera(bounds, padding);
        }

        public void animateCamera(@NonNull LatLngBounds bounds, int padding, CancelableCallback callback) {
            cameraUpdate.animateCamera(bounds, padding, callback);
        }

        public LatLng getCameraCenterPosition() {
            return cameraUpdate.getCenterPosition();
        }

        public LatLng getCameraPosition() {
            return cameraUpdate.getCameraPosition();
        }

        public float getMaxZoomLevel() {
            if (googleMap != null) {
                return googleMap.getMaxZoomLevel();
            } else if (huaweiMap != null) {
                return huaweiMap.getMaxZoomLevel();
            }
            return 20;
        }

        public void setMaxZoomPreference(float maxZoomPreference) {
            if (googleMap != null) {
                googleMap.setMaxZoomPreference(maxZoomPreference);
            } else if (huaweiMap != null) {
                huaweiMap.setMaxZoomPreference(maxZoomPreference);
            }
        }

        public void setMapTypeSatellite() {
            if (googleMap != null) {
                googleMap.setMapType(GoogleMap.MAP_TYPE_SATELLITE);
            } else if (huaweiMap != null) {
                huaweiMap.setMapType(HuaweiMap.MAP_TYPE_SATELLITE);
            }
        }

        public void setMapTypeDefault() {
            if (googleMap != null) {
                googleMap.setMapType(GoogleMap.MAP_TYPE_NORMAL);
            } else if (huaweiMap != null) {
                huaweiMap.setMapType(HuaweiMap.MAP_TYPE_NORMAL);
            }
        }

        public Marker addMarker(LatLng position, String title) {
            if (googleMap != null) {
                return (new Marker(googleMap)).addMarker(position, title);
            } else if (huaweiMap != null) {
                return (new Marker(huaweiMap)).addMarker(position, title);
            }
            return null;
        }

        public Marker addMarker(MarkerOptions options) {
            if (googleMap != null) {
                return (new Marker(googleMap)).addMarker(options);
            } else if (huaweiMap != null) {
                return (new Marker(huaweiMap)).addMarker(options);
            }
            return new Marker();
        }

        public Circle addCircle(CircleOptions options) {
            if (googleMap != null) {
                return (new Circle(googleMap)).addCircle(options);
            } else if (huaweiMap != null) {
                return (new Circle(huaweiMap)).addCircle(options);
            }
            return new Circle();
        }

        public Polyline addPolyline(PolylineOptions options) {
            if (googleMap != null) {
                return (new Polyline(googleMap)).addPolyline(options);
            } else if (huaweiMap != null) {
                return (new Polyline(huaweiMap)).addPolyline(options);
            }
            return null;
        }

        public void setOnCameraChangeListener(OnCameraChangeListener listener) {
            if (googleMap != null) {
                googleMap.setOnCameraChangeListener(cameraPosition -> {
                    if (listener != null) {
                        listener.onCameraChange();
                    }
                });
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraMoveListener(() -> {
                    if (listener != null) {
                        listener.onCameraChange();
                    }
                });
            }
        }

        public void setOnCameraMoveStartedListener(OnCameraMoveStartedListener listener) {
            if (googleMap != null) {
                googleMap.setOnCameraMoveStartedListener(i -> {
                    if (listener != null) {
                        listener.onCameraMoveStarted(i);
                    }
                });
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraMoveStartedListener(i -> {
                    if (listener != null) {
                        listener.onCameraMoveStarted(i);
                    }
                });
            }
        }

        public void setOnMarkerClickListener(OnMarkerClickListener listener) {
            if (googleMap != null) {
                googleMap.setOnMarkerClickListener(marker -> {
                    if (listener != null) {
                        return listener.onMarkerClick((new Marker(googleMap)).setMarkerObj(marker));
                    }
                    return false;
                });

            } else if (huaweiMap != null) {
                huaweiMap.setOnMarkerClickListener(marker -> {
                    if (listener != null) {
                        return listener.onMarkerClick((new Marker(huaweiMap)).setMarkerObj(marker));
                    }
                    return false;
                });
            }
        }

        public void setOnMapClickListener(OnMapClickListener listener) {
            if (googleMap != null) {
                googleMap.setOnMapClickListener(latLng -> {
                    if (listener != null) {
                        listener.onMapClick(new LatLng(latLng.latitude, latLng.longitude));
                    }
                });
            } else if (huaweiMap != null) {
                huaweiMap.setOnMapClickListener(latLng -> {
                    if (listener != null) {
                        listener.onMapClick(new LatLng(latLng.latitude, latLng.longitude));
                    }
                });
            }
        }

        public void setOnCameraMoveListener(OnCameraMoveListener listener) {
            if (googleMap != null) {
                googleMap.setOnCameraMoveListener(() -> {
                    if (listener != null) {
                        listener.onCameraMove();
                    }
                });
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraMoveListener(() -> {
                    if (listener != null) {
                        listener.onCameraMove();
                    }
                });
            }
        }

        public void setOnMapLoadedCallback(setOnMapLoadedCallback listener) {
            if (googleMap != null) {
                googleMap.setOnMapLoadedCallback(() -> {
                    if (listener != null) {
                        listener.setOnMapLoaded();
                    }
                });
            } else if (huaweiMap != null) {
                huaweiMap.setOnMapLoadedCallback(() -> {
                    if (listener != null) {
                        listener.setOnMapLoaded();
                    }
                });
            }
        }

        public Projection getProjection() {
            if (googleMap != null) {
                return new Projection(googleMap);
            } else if (huaweiMap != null) {
                return new Projection(huaweiMap);
            }
            return new Projection();
        }

        public void setOnCameraIdleListener(OnCameraIdleListener listener) {
            if (listener == null) {
                if (googleMap != null) {
                    googleMap.setOnCameraIdleListener(null);
                } else if (huaweiMap != null) {
                    huaweiMap.setOnCameraIdleListener(null);
                }
                return;
            }

            if (googleMap != null) {
                googleMap.setOnCameraIdleListener(listener::onCameraIdle);
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraIdleListener(listener::onCameraIdle);
            }
        }

        public void setOnCameraMoveCanceledListener(OnCameraMoveCanceledListener listener) {
            if (listener == null) {
                if (googleMap != null) {
                    googleMap.setOnCameraMoveCanceledListener(null);
                } else if (huaweiMap != null) {
                    huaweiMap.setOnCameraMoveCanceledListener(null);
                }
                return;
            }

            if (googleMap != null) {
                googleMap.setOnCameraMoveCanceledListener(listener::onCameraMoveCanceled);
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraMoveCanceledListener(listener::onCameraMoveCanceled);
            }
        }

        public void setPadding(int left, int top, int right, int bottom) {
            if (googleMap != null) {
                googleMap.setPadding(left, top, right, bottom);
            } else if (huaweiMap != null) {
                huaweiMap.setPadding(left, top, right, bottom);
            }
        }

        public View getView() {
            return supportFragment.getView();
        }

        public void requestLayout() {
            supportFragment.getView().requestLayout();
        }

        public void invalidate() {
            try {
                supportFragment.getView().invalidate();
            } catch (Exception e) {

            }
        }

        public ViewTreeObserver getViewTreeObserver() {
            return supportFragment.getView().getViewTreeObserver();
        }

        public void setMyLocationEnabled(boolean isMyLocationEnable) {
            if (googleMap != null) {
                googleMap.setMyLocationEnabled(isMyLocationEnable);
            } else if (huaweiMap != null) {
                huaweiMap.setMyLocationEnabled(isMyLocationEnable);
            }
        }

        public RoundCap getRoundCap() {
            if (googleMap != null) {
                return new RoundCap(googleMap);
            } else if (huaweiMap != null) {
                return new RoundCap(huaweiMap);
            }
            return new RoundCap();
        }

        public void resetMinMaxZoomPreference() {
            if (googleMap != null) {
                googleMap.resetMinMaxZoomPreference();
            } else if (huaweiMap != null) {
                huaweiMap.resetMinMaxZoomPreference();
            }
        }

        public void clear() {
            if (googleMap != null) {
                googleMap.clear();
            } else if (huaweiMap != null) {
                huaweiMap.clear();
            }
        }

        public void releaseMap() {
            clear();
            if (googleMap != null) {
                googleMap.setOnCameraIdleListener(null);
            } else if (huaweiMap != null) {
                huaweiMap.setOnCameraIdleListener(null);
            }

            googleMap = null;
            huaweiMap = null;
        }
    }
}