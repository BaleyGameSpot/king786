package com.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.drawable.Drawable;
import android.os.Build;
import android.view.View;

import androidx.annotation.ColorInt;
import androidx.annotation.DrawableRes;
import androidx.core.content.ContextCompat;
import androidx.core.content.res.ResourcesCompat;
import androidx.core.graphics.drawable.DrawableCompat;

import com.google.android.gms.maps.model.BitmapDescriptor;
import com.google.android.gms.maps.model.BitmapDescriptorFactory;

public class VectorUtils {
    public static void manageVectorImage(Context context, View view, int orgResId, int compactResId) {
        final int sdk = Build.VERSION.SDK_INT;
        if (sdk < Build.VERSION_CODES.M) {
            view.setBackgroundDrawable(ContextCompat.getDrawable(context, compactResId));
        } else {
            view.setBackground(ContextCompat.getDrawable(context, orgResId));
        }
    }

    public static BitmapDescriptor vectorToBitmap(Context context, @DrawableRes int id, @ColorInt int color) {
        Drawable vectorDrawable = ResourcesCompat.getDrawable(context.getResources(), id, null);
        assert vectorDrawable != null;
        Bitmap bitmap = Bitmap.createBitmap(vectorDrawable.getIntrinsicWidth(), vectorDrawable.getIntrinsicHeight(), Bitmap.Config.ARGB_8888);
        Canvas canvas = new Canvas(bitmap);
        vectorDrawable.setBounds(0, 0, canvas.getWidth(), canvas.getHeight());
        if (color > 0) {
            DrawableCompat.setTint(vectorDrawable, color);
        }
        vectorDrawable.draw(canvas);
        return BitmapDescriptorFactory.fromBitmap(bitmap);
    }
}
