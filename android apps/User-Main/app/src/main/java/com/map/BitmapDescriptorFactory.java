package com.map;

import android.graphics.Bitmap;

public class BitmapDescriptorFactory {

    public static BitmapDescriptor fromResource(int resourceId) {
        return new BitmapDescriptor(resourceId);
    }

    public static BitmapDescriptor fromBitmap(Bitmap image) {
        return new BitmapDescriptor(image);
    }

    public static class BitmapDescriptor {
        int resourceId = -1;
        public Bitmap image;

        public BitmapDescriptor(int resourceId) {
            this.resourceId = resourceId;
        }

        public BitmapDescriptor(Bitmap image) {
            this.image = image;
        }
    }
}
