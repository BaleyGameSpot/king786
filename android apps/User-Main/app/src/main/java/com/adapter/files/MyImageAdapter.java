package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.viewpager.widget.PagerAdapter;

import com.act.homescreen23.adapter.HomeUtils;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.anim.loader.AVLoadingIndicatorView;

import java.util.ArrayList;
import java.util.HashMap;

public class MyImageAdapter extends PagerAdapter {

    private final Context context;
    @Nullable
    private final ArrayList<HashMap<String, String>> arrayList;
    @Nullable
    private final OnActivePosition onActivePosition;
    int itemWidth, itemHeight;

    public MyImageAdapter(@NonNull Context context, @Nullable ArrayList<HashMap<String, String>> arrayList, @Nullable OnActivePosition onActivePosition) {
        this.context = context;
        this.arrayList = arrayList;
        this.onActivePosition = onActivePosition;

        this.itemWidth = (int) Utils.getScreenPixelWidth(context);
        this.itemHeight = (int) (itemWidth / 1.33333333333);
    }

    @NonNull
    @Override
    public Object instantiateItem(ViewGroup container, int position) {
        View view = LayoutInflater.from(container.getContext()).inflate(R.layout.item_slider_images, container, false);

        RelativeLayout rlMainSliderView = view.findViewById(R.id.rlMainSliderView);
        AVLoadingIndicatorView videoLoaderView = view.findViewById(R.id.videoLoaderView);
        videoLoaderView.setVisibility(View.GONE);
        AppCompatImageView imgPlayIcon = view.findViewById(R.id.imgPlayIcon);
        imgPlayIcon.setVisibility(View.GONE);
        AppCompatImageView ivProductImage = view.findViewById(R.id.ivProductImage);
        ivProductImage.setVisibility(View.GONE);
        AppCompatImageView thumbnailImage = view.findViewById(R.id.thumbnailImage);
        thumbnailImage.setVisibility(View.GONE);

        HashMap<String, String> data = arrayList.get(position);

        if (data.get("eFileType").equalsIgnoreCase("Video")) {
            thumbnailImage.setVisibility(View.VISIBLE);
            //
            RelativeLayout.LayoutParams mParamsThum = (RelativeLayout.LayoutParams) thumbnailImage.getLayoutParams();
            mParamsThum.width = itemWidth;
            mParamsThum.height = itemHeight;
            thumbnailImage.setLayoutParams(mParamsThum);
            //
            HomeUtils.loadImg(context, thumbnailImage, data.get("ThumbImage"), R.drawable.ic_novideo__icon, true, itemWidth, itemHeight);
            VectorUtils.manageVectorImage(context, imgPlayIcon, R.drawable.ic_play_button, R.drawable.ic_play_button_compat);
            imgPlayIcon.setVisibility(View.VISIBLE);

        } else {
            ivProductImage.setVisibility(View.VISIBLE);
            //
            RelativeLayout.LayoutParams mParams = (RelativeLayout.LayoutParams) ivProductImage.getLayoutParams();
            mParams.width = itemWidth;
            mParams.height = itemHeight;
            ivProductImage.setLayoutParams(mParams);
            //
            HomeUtils.loadImg(context, ivProductImage, data.get("vImage"), R.color.imageBg, true, itemWidth, itemHeight);
        }

        rlMainSliderView.setOnClickListener(v -> {
            if (onActivePosition != null) {
                onActivePosition.onActivePosition(position);
            }
        });

        container.addView(view);
        return view;
    }

    @Override
    public void destroyItem(ViewGroup container, int position, @NonNull Object object) {
        container.removeView((View) object);
    }

    @Override
    public int getCount() {
        return arrayList != null ? arrayList.size() : 0;
    }

    @Override
    public boolean isViewFromObject(@NonNull View view, @NonNull Object o) {
        return view == ((View) o);
    }

    public interface OnActivePosition {
        void onActivePosition(int pos);
    }
}