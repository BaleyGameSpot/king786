package com.dialogs;

import android.app.Activity;
import android.content.Context;
import android.content.pm.PackageManager;
import android.content.res.Configuration;
import android.graphics.Insets;
import android.net.Uri;
import android.os.Build;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.WindowInsets;
import android.view.WindowMetrics;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.annotation.OptIn;
import androidx.appcompat.app.AlertDialog;
import androidx.core.content.ContextCompat;
import androidx.media3.common.MediaItem;
import androidx.media3.common.Player;
import androidx.media3.common.util.UnstableApi;
import androidx.media3.datasource.DefaultDataSource;
import androidx.media3.datasource.DefaultHttpDataSource;
import androidx.media3.datasource.FileDataSource;
import androidx.media3.datasource.cache.CacheDataSink;
import androidx.media3.datasource.cache.CacheDataSource;
import androidx.media3.exoplayer.ExoPlayer;
import androidx.media3.exoplayer.source.MediaSource;
import androidx.media3.exoplayer.source.ProgressiveMediaSource;

import com.bumptech.glide.Glide;
import com.bumptech.glide.request.RequestOptions;
import com.general.files.Closure;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.DesignPlayVideoBinding;
import com.buddyverse.main.databinding.DialogSelectnavigationViewBinding;
import com.utils.LayoutDirection;
import com.utils.MyUtils;
import com.utils.Utils;

import java.util.Objects;

public class MyCommonDialog {

    public static void navigationDialog(@NonNull Context mContext, @NonNull GeneralFunctions generalFunc, @Nullable final Closure googleClosure, @Nullable final Closure wazeClosure) {
        AlertDialog.Builder builder = new AlertDialog.Builder(mContext);
        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull DialogSelectnavigationViewBinding binding = DialogSelectnavigationViewBinding.inflate(inflater, null, false);

        builder.setView(binding.getRoot());
        AlertDialog alertDialog = builder.create();
        //-----------
        binding.titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_CHOOSE_OPTION"));
        binding.googleMapTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NAVIGATION_GOOGLE_MAP"));
        binding.wazeMapTxt.setText(generalFunc.retrieveLangLBl("", "LBL_NAVIGATION_WAZE"));

        binding.cancelImg.setOnClickListener(v -> alertDialog.dismiss());
        binding.googleMapArea.setOnClickListener(v -> {
            alertDialog.dismiss();
            if (googleClosure != null) {
                googleClosure.exec();
            }
        });
        binding.wazeMapArea.setOnClickListener(v -> {
            alertDialog.dismiss();
            if (wazeClosure != null) {
                wazeClosure.exec();
            }
        });

        //-----------
        if (generalFunc.isRTLmode()) {
            binding.googleArrow.setRotation(180);
            binding.wazeArrow.setRotation(180);
        }
        LayoutDirection.setLayoutDirection(alertDialog);
        alertDialog.setCancelable(false);
        alertDialog.setCanceledOnTouchOutside(false);
        Objects.requireNonNull(alertDialog.getWindow()).setBackgroundDrawable(ContextCompat.getDrawable(mContext, R.drawable.all_roundcurve_card));
        alertDialog.show();
    }

    public static boolean isPackageInstalled(String packageName, PackageManager packageManager) {
        try {
            packageManager.getPackageInfo(packageName, 0);
            return true;
        } catch (PackageManager.NameNotFoundException e) {
            return false;
        }
    }

    @OptIn(markerClass = UnstableApi.class)
    public static void showVideoDialog(@NonNull Activity act, @NonNull String thumbUrl, @NonNull String videoUrl) {
        AlertDialog builder = new AlertDialog.Builder(act, android.R.style.Theme_Black_NoTitleBar_Fullscreen).create();

        LayoutInflater inflater = (LayoutInflater) act.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull DesignPlayVideoBinding binding = DesignPlayVideoBinding.inflate(inflater, null, false);

        binding.mProgressBar.setVisibility(View.VISIBLE);

        int width, height;
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.R) {
            WindowMetrics windowMetrics = act.getWindowManager().getCurrentWindowMetrics();
            Insets insets = windowMetrics.getWindowInsets().getInsetsIgnoringVisibility(WindowInsets.Type.systemBars());
            width = windowMetrics.getBounds().width() - insets.left - insets.right;
            height = windowMetrics.getBounds().height() - insets.top - insets.bottom;
        } else {
            DisplayMetrics dm = new DisplayMetrics();
            act.getWindowManager().getDefaultDisplay().getMetrics(dm);
            height = dm.heightPixels;
            width = dm.widthPixels;
        }
        binding.playerView.setMinimumWidth(width);
        binding.playerView.setMinimumHeight(height);

        CacheDataSink.Factory cacheSink = new CacheDataSink.Factory()
                .setCache(MyUtils.getSimpleCache());
        DefaultDataSource.Factory upstreamFactory = new DefaultDataSource.Factory(act, new DefaultHttpDataSource.Factory());
        FileDataSource.Factory downStreamFactory = new FileDataSource.Factory();
        CacheDataSource.Factory cacheDataSourceFactory = new CacheDataSource.Factory()
                .setCache(MyUtils.getSimpleCache())
                .setCacheWriteDataSinkFactory(cacheSink)
                .setCacheReadDataSourceFactory(downStreamFactory)
                .setUpstreamDataSourceFactory(upstreamFactory)
                .setFlags(CacheDataSource.FLAG_IGNORE_CACHE_ON_ERROR);

        MediaItem mediaItem = MediaItem.fromUri(Uri.parse(videoUrl));
        MediaSource mediaSource = new ProgressiveMediaSource.Factory(cacheDataSourceFactory).createMediaSource(mediaItem);

        ExoPlayer player = new ExoPlayer.Builder(act).build();
        player.setMediaSource(mediaSource);
        binding.playerView.setPlayer(player);
        player.prepare();

        String imageUrl = Utils.getResizeImgURL(act, thumbUrl, ((int) Utils.getScreenPixelWidth(act)) -
                Utils.dipToPixels(act, 30), 0, Utils.getScreenPixelHeight(act) - Utils.dipToPixels(act, 30));
        Glide.with(act)
                .load(imageUrl)
                .apply(new RequestOptions().placeholder(R.drawable.ic_novideo__icon).error(R.drawable.ic_novideo__icon))
                .into(binding.thumbnailImage);
        player.addListener(new Player.Listener() {
            @Override
            public void onEvents(Player player, Player.Events events) {
                Player.Listener.super.onEvents(player, events);
                if (player.getPlaybackState() == ExoPlayer.STATE_BUFFERING) {
                    binding.thumbnailImage.setVisibility(View.GONE);
                    binding.mProgressBar.setVisibility(View.VISIBLE);
                } else if (player.getPlaybackState() == ExoPlayer.STATE_READY) {
                    binding.mProgressBar.setVisibility(View.GONE);
                    binding.thumbnailImage.setVisibility(View.GONE);
                } else if (player.getPlaybackState() == ExoPlayer.STATE_IDLE) {
                    binding.thumbnailImage.setVisibility(View.VISIBLE);

                }
            }
        });

        if (act.getResources().getConfiguration().orientation == Configuration.ORIENTATION_LANDSCAPE) {
            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) binding.playerView.getLayoutParams();
            params.width = params.MATCH_PARENT;
            params.height = params.MATCH_PARENT;
            binding.playerView.setLayoutParams(params);
        }

        binding.closeVideoView.setOnClickListener(v -> {
            player.release();
            builder.dismiss();
        });

        player.setPlayWhenReady(true);
        builder.setView(binding.getRoot());
        builder.show();
    }
}