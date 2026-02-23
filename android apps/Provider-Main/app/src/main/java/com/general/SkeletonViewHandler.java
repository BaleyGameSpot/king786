package com.general;

import android.view.View;

import androidx.recyclerview.widget.RecyclerView;

import com.ethanhua.skeleton.Skeleton;
import com.ethanhua.skeleton.SkeletonScreen;
import com.buddyverse.providers.R;

public class SkeletonViewHandler {

    private static SkeletonViewHandler instance;
    private View bindView;

    private SkeletonScreen skeletonScreen;
    private RecyclerView.Adapter<RecyclerView.ViewHolder> skeletonAdapter;

    private boolean isRecyclerview;
    private int skeletonLayout, durationConfig, color, angleConfig;

    public static SkeletonViewHandler getInstance() {
        if (instance == null) {
            instance = new SkeletonViewHandler();
        }
        return instance;
    }

    public void ShowNormalSkeletonView(View bindView, int skeletonLayout) {
        this.bindView = bindView;
        this.skeletonLayout = skeletonLayout;
        this.durationConfig = 1000;
        this.color = R.color.shimmer_color;
        this.angleConfig = 0;

        this.isRecyclerview = false;
        show();
    }

    public void ShowCustomNormalSkeletonView(View bindView, int skeletonLayout, int durationConfig, int color, int angleConfig) {
        this.bindView = bindView;
        this.skeletonLayout = skeletonLayout;
        this.durationConfig = durationConfig;
        this.color = color;
        this.angleConfig = angleConfig;

        this.isRecyclerview = false;
        show();
    }

    public void showListSkeletonView(View bindView, int skeletonLayout, RecyclerView.Adapter<RecyclerView.ViewHolder> skeletonAdapter) {
        this.bindView = bindView;
        this.skeletonLayout = skeletonLayout;
        this.skeletonAdapter = skeletonAdapter;
        this.durationConfig = 1000;
        this.color = R.color.shimmer_color;
        this.angleConfig = 0;

        this.isRecyclerview = true;
        show();
    }

    public void showCustomListSkeletonView(View bindView, int skeletonLayout, int durationConfig, int color, int angleConfig, RecyclerView.Adapter<RecyclerView.ViewHolder> skeletonAdapter) {
        this.bindView = bindView;
        this.skeletonLayout = skeletonLayout;
        this.durationConfig = durationConfig;
        this.color = color;
        this.angleConfig = angleConfig;
        this.skeletonAdapter = skeletonAdapter;

        this.isRecyclerview = true;
        show();
    }

    private void show() {
        if (bindView == null || skeletonLayout == 0) {
            return;
        }
        if (isRecyclerview) {
            showListSkeletonView();
        } else {
            hideSkeletonView();
            showNormalSkeletonView();
        }
    }

    private void showNormalSkeletonView() {
        skeletonScreen = Skeleton.bind(bindView)
                .load(skeletonLayout).color(color)
                .duration(durationConfig).angle(angleConfig).show();
    }

    private void showListSkeletonView() {
        if (skeletonAdapter == null) {
            skeletonScreen = Skeleton.bind(bindView)
                    .load(skeletonLayout)
                    .color(R.color.shimmer_color)
                    .duration(durationConfig).angle(angleConfig).show();
        } else {
            skeletonScreen = Skeleton.bind((RecyclerView) bindView)
                    .adapter(skeletonAdapter)
                    .load(skeletonLayout)
                    .color(color)
                    .duration(durationConfig).angle(angleConfig).show();
        }
    }

    public void hideSkeletonView() {
        if (skeletonScreen != null) {
            skeletonScreen.hide();
            skeletonScreen = null;
        }
    }
}