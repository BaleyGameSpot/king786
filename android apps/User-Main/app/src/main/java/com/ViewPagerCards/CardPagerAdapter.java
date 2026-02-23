package com.ViewPagerCards;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.drawable.BitmapDrawable;
import android.os.Build;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import androidx.cardview.widget.CardView;
import androidx.viewpager.widget.PagerAdapter;

import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Logger;

import java.util.ArrayList;
import java.util.List;

public class CardPagerAdapter extends PagerAdapter implements CardAdapter {

    private int bannerWidth, bannerHeight;
    private List<CardView> mViews;
    private List<String> mData;
    private float mBaseElevation;
    Context mContext;

    public CardPagerAdapter() {
        mData = new ArrayList<>();
        mViews = new ArrayList<>();
    }

    public void addCardItem(String item, Context context, int bannerWidth, int bannerHeight) {
        mViews.add(null);
        mData.add(item);
        this.mContext = context;
        this.bannerWidth = bannerWidth;
        this.bannerHeight = bannerHeight;
    }

    public float getBaseElevation() {
        return mBaseElevation;
    }

    @Override
    public CardView getCardViewAt(int position) {
        return mViews.get(position);
    }

    @Override
    public int getCount() {
        return mData.size();
    }

    @Override
    public boolean isViewFromObject(View view, Object object) {
        return view == object;
    }

    @Override
    public Object instantiateItem(ViewGroup container, int position) {
        View view = LayoutInflater.from(container.getContext()).inflate(R.layout.adapter, container, false);
        container.addView(view);
        bind(mData.get(position), view);
        CardView cardView = view.findViewById(R.id.cardView);
        ImageView imageView = view.findViewById(R.id.imagView);

        if (mBaseElevation == 0) {
            mBaseElevation = cardView.getCardElevation();
        }

        position = position % mViews.size();

        Logger.d("BannerimageURL_2", "::" + mData.get(position));

        CardView.LayoutParams bannerLayoutParams = (CardView.LayoutParams) imageView.getLayoutParams();
        bannerLayoutParams.width = bannerWidth;
        bannerLayoutParams.height = bannerHeight;
        imageView.setLayoutParams(bannerLayoutParams);

        new LoadImage.builder(LoadImage.bind(mData.get(position)), imageView).setPicassoListener(new LoadImage.PicassoListener() {
            @Override
            public void onSuccess() {
                try {
                    if (Build.VERSION.SDK_INT < Build.VERSION_CODES.LOLLIPOP) {
                        imageView.invalidate();
                        BitmapDrawable drawable = (BitmapDrawable) imageView.getDrawable();
                        Bitmap bitmap = drawable.getBitmap();
                        cardView.setPreventCornerOverlap(false);

                        RoundCornerDrawable round = new RoundCornerDrawable(bitmap, mContext.getResources().getDimension(R.dimen._15sdp), 5);
                        imageView.setVisibility(View.GONE);
                        cardView.setBackground(round);
                    }
                } catch (Exception e) {
                    Logger.e("Exception","::"+e.getMessage());
                }
            }

            @Override
            public void onError() {

            }
        }).build();
        cardView.setMaxCardElevation(mBaseElevation * MAX_ELEVATION_FACTOR);
        mViews.set(position, cardView);
        return view;
    }

    @Override
    public void destroyItem(ViewGroup container, int position, Object object) {
        container.removeView((View) object);
        mViews.set(position, null);
    }

    private void bind(String item, View view) {

    }

    private void setMargins(View view, int left, int top, int right, int bottom) {
        if (view.getLayoutParams() instanceof ViewGroup.MarginLayoutParams) {
            ViewGroup.MarginLayoutParams p = (ViewGroup.MarginLayoutParams) view.getLayoutParams();
            p.setMargins(left, top, right, bottom);
            view.requestLayout();
        }
    }

}
