package com.general.files;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.PorterDuff;
import android.graphics.PorterDuffXfermode;
import android.util.DisplayMetrics;
import android.view.View;

import com.buddyverse.main.R;
import com.utils.Utils;

public class PaintView extends View {
    private final float arcLeft;

    @SuppressLint("ResourceAsColor")
    public PaintView(Context context) {
        super(context);
        DisplayMetrics displayMetrics = new DisplayMetrics();

        ((Activity) getContext()).getWindowManager().getDefaultDisplay().getMetrics(displayMetrics);

        arcLeft = pxFromDp(context, Utils.pxToDp(getContext(), getResources().getDimensionPixelSize(R.dimen._69sdp)));
    }

    private static float pxFromDp(final Context context, final float dp) {
        return dp * context.getResources().getDisplayMetrics().density;
    }

    @SuppressLint("DrawAllocation")
    @Override
    protected void onDraw(Canvas canvas) {
        super.onDraw(canvas);

        Path mPath = new Path();
        mPath.addRect(getLeft(), getTop(), getRight(), getBottom(), Path.Direction.CW);
        mPath.addCircle(getWidth() / 2f, getHeight() / 2f, arcLeft, Path.Direction.CCW);

        Paint mPaint = new Paint();
        mPaint.setAntiAlias(true);
        mPaint.setStrokeCap(Paint.Cap.ROUND);
        mPaint.setXfermode(new PorterDuffXfermode(PorterDuff.Mode.SRC));
        int color = getResources().getColor(R.color.transparent_70);
        mPaint.setColor(color);
        canvas.drawPath(mPath, mPaint);
    }
}