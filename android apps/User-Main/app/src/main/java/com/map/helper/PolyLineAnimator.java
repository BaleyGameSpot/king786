package com.map.helper;

import android.animation.Animator;
import android.animation.ValueAnimator;
import android.content.Context;
import android.graphics.Color;
import android.view.animation.DecelerateInterpolator;

import androidx.annotation.NonNull;

import com.map.GeoMapLoader;
import com.map.Polyline;
import com.map.models.LatLng;
import com.map.models.PolylineOptions;
import com.utils.Logger;

import java.util.ArrayList;

public class PolyLineAnimator {
    public static int NON_HIGHLIGHT_COLOR = Color.parseColor("#FFA7A6A6");
    public static int HIGHLIGHT_COLOR = Color.BLACK;

    private static PolyLineAnimator polyLineAnimator;
    private Polyline backgroundPolyline;
    private Polyline foregroundPolyline;
    private PolylineOptions optionsForeground;
    private GeoMapLoader.GeoMap geoMap = null;

    private ValueAnimator polylineValueAnimator;
    private int animCount = 0, lastPoints = 0;
    private final ArrayList<LatLng> latLngArrayList = new ArrayList<>();

    public static PolyLineAnimator getInstance() {
        if (polyLineAnimator == null) polyLineAnimator = new PolyLineAnimator();
        return polyLineAnimator;
    }

    public void stopRouteAnim() {
        try {
            if (polylineValueAnimator != null) {
                polylineValueAnimator.removeAllListeners();
                polylineValueAnimator.end();
                polylineValueAnimator.cancel();
                polylineValueAnimator = null;
            }

            if (backgroundPolyline != null) {
                backgroundPolyline.remove();
                backgroundPolyline = null;
            }
            if (foregroundPolyline != null) {
                foregroundPolyline.remove();
                foregroundPolyline = null;
            }
            if (geoMap != null) {
                geoMap = null;
            }

            if (optionsForeground != null) {
                optionsForeground = null;
            }
            PolyLineAnimator.polyLineAnimator = null;
        } catch (Exception e) {
            Logger.e("PolyLineP", "::Exception::" + e.getMessage());
        }
    }

    private void resetPolyLines() {
        //Reset the polylines
        if (foregroundPolyline != null) {
            foregroundPolyline.remove();
            foregroundPolyline = null;
        }
        if (backgroundPolyline != null) {
            backgroundPolyline.remove();
            backgroundPolyline = null;
        }

        PolylineOptions optionsBackground = new PolylineOptions().color(NON_HIGHLIGHT_COLOR).width(5);
        backgroundPolyline = geoMap.addPolyline(optionsBackground);

        optionsForeground = new PolylineOptions().color(HIGHLIGHT_COLOR).width(5);
        foregroundPolyline = geoMap.addPolyline(optionsForeground);
    }

    public void animateRoute(GeoMapLoader.GeoMap geoMap, ArrayList<LatLng> routePointList, Context mContext) {
        this.geoMap = geoMap;
        resetPolyLines();

        if (polylineValueAnimator != null) {
            polylineValueAnimator.removeAllListeners();
            polylineValueAnimator.end();
            polylineValueAnimator.cancel();
            polylineValueAnimator = null;
        }

        polylineValueAnimator = ValueAnimator.ofInt(0, 100);
        polylineValueAnimator.setDuration(3000);
        polylineValueAnimator.setRepeatCount(ValueAnimator.INFINITE);
        polylineValueAnimator.setRepeatMode(ValueAnimator.RESTART);
        polylineValueAnimator.setInterpolator(new DecelerateInterpolator());
        polylineValueAnimator.addUpdateListener(animation -> {
            int percentValue = (int) animation.getAnimatedValue();
            int size = routePointList.size();
            int newPoints = (int) (size * (percentValue / 100.0f));

            if (lastPoints != newPoints) {
                lastPoints = newPoints;

                latLngArrayList.clear();
                latLngArrayList.addAll(routePointList.subList(0, newPoints));

                if (animCount % 2 == 0) {
                    if (foregroundPolyline != null) {
                        foregroundPolyline.setPoints(latLngArrayList);
                    }
                } else {
                    if (backgroundPolyline != null) {
                        backgroundPolyline.setPoints(latLngArrayList);
                    }
                }
            }
        });

        polylineValueAnimator.addListener(new Animator.AnimatorListener() {
            @Override
            public void onAnimationStart(@NonNull Animator animation) {
            }

            @Override
            public void onAnimationEnd(@NonNull Animator animation) {
            }

            @Override
            public void onAnimationCancel(@NonNull Animator animation) {
            }

            @Override
            public void onAnimationRepeat(@NonNull Animator animation) {
                animCount++;

                if (animCount % 2 == 0) {
                    if (foregroundPolyline != null) {
                        foregroundPolyline.remove();
                        foregroundPolyline = null;
                    }

                    optionsForeground = new PolylineOptions().color(HIGHLIGHT_COLOR).width(5);
                    foregroundPolyline = geoMap.addPolyline(optionsForeground);

                } else {
                    if (backgroundPolyline != null) {
                        backgroundPolyline.remove();
                        backgroundPolyline = null;
                    }

                    PolylineOptions optionsBackground = new PolylineOptions().color(NON_HIGHLIGHT_COLOR).width(5);
                    backgroundPolyline = geoMap.addPolyline(optionsBackground);
                }

            }
        });
        polylineValueAnimator.start();
    }
}