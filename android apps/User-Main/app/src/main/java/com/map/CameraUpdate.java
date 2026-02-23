package com.map;

import com.google.android.gms.maps.GoogleMap;
import com.huawei.hms.maps.HuaweiMap;
import com.map.minterface.CancelableCallback;
import com.map.models.LatLng;
import com.map.models.LatLngBounds;
import com.utils.Utils;

public class CameraUpdate {
    GoogleMap gMap;
    HuaweiMap hMap;

    public CameraUpdate() {
    }

    public CameraUpdate(GoogleMap gMap) {
        this.gMap = gMap;
    }

    public CameraUpdate(HuaweiMap hMap) {
        this.hMap = hMap;
    }

    public void moveCamera(LatLng position) {
        if (gMap != null) {
            gMap.moveCamera(com.google.android.gms.maps.CameraUpdateFactory.newCameraPosition(new com.google.android.gms.maps.model.CameraPosition.Builder().target(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude))
                    .zoom(position.zoom).build()));
        } else if (hMap != null) {
            hMap.moveCamera(com.huawei.hms.maps.CameraUpdateFactory.newCameraPosition(new com.huawei.hms.maps.model.CameraPosition.Builder().target(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude))
                    .zoom(position.zoom).build()));
        }
    }

    public void animateCamera(LatLng position) {
        if (gMap != null) {
            gMap.animateCamera(com.google.android.gms.maps.CameraUpdateFactory.newCameraPosition(new com.google.android.gms.maps.model.CameraPosition.Builder().target(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude))
                    .zoom(position.zoom).build()));
        } else if (hMap != null) {
            hMap.animateCamera(com.huawei.hms.maps.CameraUpdateFactory.newCameraPosition(new com.huawei.hms.maps.model.CameraPosition.Builder().target(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude))
                    .zoom(position.zoom).build()));
        }
    }

    public LatLng getCenterPosition() {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLng centerPoint = gMap.getProjection().getVisibleRegion().latLngBounds.getCenter();
            return new LatLng(centerPoint.latitude, centerPoint.longitude, gMap.getCameraPosition().zoom);
        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLng centerPoint = hMap.getProjection().getVisibleRegion().latLngBounds.getCenter();
            return new LatLng(centerPoint.latitude, centerPoint.longitude, hMap.getCameraPosition().zoom);
        }

        return new LatLng(0.0, 0.0, Utils.defaultZomLevel);
    }

    public LatLng getCameraPosition() {
        if (gMap != null) {
            return new LatLng(gMap.getCameraPosition().target.latitude, gMap.getCameraPosition().target.longitude, gMap.getCameraPosition().zoom);
        } else if (hMap != null) {
            return new LatLng(hMap.getCameraPosition().target.latitude, hMap.getCameraPosition().target.longitude, hMap.getCameraPosition().zoom);
        }
        return new LatLng(0.0, 0.0, Utils.defaultZomLevel);
    }

    public void moveCamera(LatLngBounds bounds, int width, int height, int padding) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.moveCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding));

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.moveCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding));
        }
    }

    public void moveCamera(LatLngBounds bounds, int padding) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.moveCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding));

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.moveCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding));
        }
    }

    public void animateCamera(LatLngBounds bounds, int width, int height, int padding) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.animateCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding));

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.animateCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding));
        }
    }

    public void animateCamera(LatLngBounds bounds, int width, int height, int padding, CancelableCallback callback) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.animateCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding), new GoogleMap.CancelableCallback() {
                @Override
                public void onCancel() {
                    if (callback != null) {
                        callback.onCancel();
                    }
                }

                @Override
                public void onFinish() {
                    if (callback != null) {
                        callback.onFinish();
                    }
                }
            });

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.animateCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), width, height, padding), new HuaweiMap.CancelableCallback() {
                @Override
                public void onFinish() {
                    if (callback != null) {
                        callback.onFinish();
                    }
                }

                @Override
                public void onCancel() {
                    if (callback != null) {
                        callback.onCancel();
                    }
                }
            });
        }
    }

    public void animateCamera(LatLngBounds bounds, int padding) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.animateCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding));

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.animateCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding));
        }
    }

    public void animateCamera(LatLngBounds bounds, int padding, CancelableCallback callback) {
        if (gMap != null) {
            com.google.android.gms.maps.model.LatLngBounds.Builder builder = new com.google.android.gms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                if (position == null) {
                    continue;
                }
                builder.include(new com.google.android.gms.maps.model.LatLng(position.latitude, position.longitude));
            }

            gMap.animateCamera(com.google.android.gms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding), new GoogleMap.CancelableCallback() {
                @Override
                public void onCancel() {
                    if (callback != null) {
                        callback.onCancel();
                    }
                }

                @Override
                public void onFinish() {
                    if (callback != null) {
                        callback.onFinish();
                    }
                }
            });

        } else if (hMap != null) {
            com.huawei.hms.maps.model.LatLngBounds.Builder builder = new com.huawei.hms.maps.model.LatLngBounds.Builder();

            for (LatLng position : bounds.getPoints()) {
                builder.include(new com.huawei.hms.maps.model.LatLng(position.latitude, position.longitude));
            }

            hMap.animateCamera(com.huawei.hms.maps.CameraUpdateFactory.newLatLngBounds(builder.build(), padding), new HuaweiMap.CancelableCallback() {
                @Override
                public void onFinish() {
                    if (callback != null) {
                        callback.onFinish();
                    }
                }

                @Override
                public void onCancel() {
                    if (callback != null) {
                        callback.onCancel();
                    }
                }
            });
        }
    }
}
