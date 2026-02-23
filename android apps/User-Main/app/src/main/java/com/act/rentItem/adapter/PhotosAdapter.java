package com.act.rentItem.adapter;

import android.graphics.Bitmap;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.widget.AppCompatImageView;
import androidx.viewpager.widget.PagerAdapter;

import com.bumptech.glide.Glide;
import com.bumptech.glide.load.DataSource;
import com.bumptech.glide.load.engine.GlideException;
import com.bumptech.glide.request.RequestListener;
import com.bumptech.glide.request.RequestOptions;
import com.bumptech.glide.request.target.Target;
import com.general.files.MyApp;
import com.google.android.material.imageview.ShapeableImageView;
import com.google.android.material.shape.CornerFamily;
import com.buddyverse.main.R;
import com.utils.Utils;
import com.utils.VectorUtils;
import com.view.anim.loader.AVLoadingIndicatorView;

import java.util.ArrayList;
import java.util.HashMap;

public class PhotosAdapter extends PagerAdapter {

    private final ArrayList<HashMap<String, String>> arrayList;
    private final OnActivePosition onActivePosition;
    private final int bannerWidth, bannerHeight;
    private Boolean semi_round;
    private Boolean top_round;

    public PhotosAdapter(ArrayList<HashMap<String, String>> arrayList, int bannerWidth, int bannerHeight, OnActivePosition onActivePosition, Boolean semi_round, boolean top_round) {
        this.arrayList = arrayList;
        this.bannerWidth = bannerWidth;
        this.bannerHeight = bannerHeight;
        this.onActivePosition = onActivePosition;
        this.semi_round = semi_round;
        this.top_round = top_round;
    }

    @NonNull
    @Override
    public Object instantiateItem(ViewGroup container, int position) {
        View view = LayoutInflater.from(container.getContext()).inflate(R.layout.item_slider_images, container, false);

        RelativeLayout rlMainSliderView = view.findViewById(R.id.rlMainSliderView);
        AVLoadingIndicatorView videoLoaderView = view.findViewById(R.id.videoLoaderView);
        AppCompatImageView imgPlayIcon = view.findViewById(R.id.imgPlayIcon);
        ShapeableImageView ivProductImage = view.findViewById(R.id.ivProductImage);
        AppCompatImageView thumbnailImage = view.findViewById(R.id.thumbnailImage);

        if (semi_round) {
            float radius = container.getContext().getResources().getDimension(R.dimen._10sdp);
            ivProductImage.setShapeAppearanceModel(ivProductImage.getShapeAppearanceModel()
                    .toBuilder()
                    .setBottomLeftCorner(CornerFamily.ROUNDED, radius)
                    .setBottomRightCorner(CornerFamily.ROUNDED, radius)
                    .build());
        } else if (top_round) {
            float radius = container.getContext().getResources().getDimension(R.dimen._7sdp);
            ivProductImage.setScaleType(ImageView.ScaleType.CENTER_CROP);
            ivProductImage.setShapeAppearanceModel(ivProductImage.getShapeAppearanceModel()
                    .toBuilder()
                    .setTopLeftCorner(CornerFamily.ROUNDED, radius)
                    .setTopRightCorner(CornerFamily.ROUNDED, radius)
                    .build());
        } else {
            float radius = container.getContext().getResources().getDimension(R.dimen._10sdp);
            ivProductImage.setShapeAppearanceModel(ivProductImage.getShapeAppearanceModel()
                    .toBuilder()
                    .setAllCorners(CornerFamily.ROUNDED, radius)
                    .build());
        }

        HashMap<String, String> data = arrayList.get(position);


        if (data.get("eFileType").equalsIgnoreCase("Video")) {
            videoLoaderView.setVisibility(View.GONE);
            ivProductImage.setVisibility(View.GONE);
            if (!TextUtils.isEmpty(data.get("ThumbImage"))) {
                Glide.with(container.getContext())
                        .asBitmap()
                        .load(Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), data.get("ThumbImage"), 0, bannerWidth, bannerHeight))
                        .apply(new RequestOptions()
                                .placeholder(R.drawable.ic_novideo__icon).error(R.drawable.ic_novideo__icon))
                        .listener(new RequestListener<Bitmap>() {
                            @Override
                            public boolean onLoadFailed(@Nullable GlideException e, Object model, Target<Bitmap> target, boolean isFirstResource) {

                                return false;
                            }

                            @Override
                            public boolean onResourceReady(Bitmap resource, Object model, Target<Bitmap> target, DataSource dataSource, boolean isFirstResource) {
                                return false;
                            }
                        })
                        .into(thumbnailImage);
            }
            thumbnailImage.setVisibility(View.VISIBLE);
            VectorUtils.manageVectorImage(container.getContext(), imgPlayIcon, R.drawable.ic_play_button, R.drawable.ic_play_button_compat);
            imgPlayIcon.setVisibility(View.VISIBLE);


        } else {
            if (!TextUtils.isEmpty(data.get("vImage"))) {
                Glide.with(container.getContext())
                        .load(Utils.getResizeImgURL(MyApp.getInstance().getCurrentAct(), data.get("vImage"), bannerWidth, bannerHeight))
                        .apply(new RequestOptions().placeholder(R.color.imageBg).error(R.color.imageBg))
                        .into(ivProductImage);

            }

            videoLoaderView.setVisibility(View.GONE);
            imgPlayIcon.setVisibility(View.GONE);
            ivProductImage.setVisibility(View.VISIBLE);

        }
        rlMainSliderView.setOnClickListener(v -> {
            if (onActivePosition != null) {
                onActivePosition.onActivePosition(position, data.get("vImage"));
            }
        });

        container.addView(view);
        return view;
    }

    @Override
    public int getCount() {
        return arrayList.size();
    }

    @Override
    public boolean isViewFromObject(@NonNull View view, @NonNull Object o) {
        return view == ((View) o);
    }

    @Override
    public void destroyItem(ViewGroup container, int position, Object object) {
        container.removeView((View) object);
    }

    public interface OnActivePosition {
        void onActivePosition(int pos, String finalImageUrl);
    }
}