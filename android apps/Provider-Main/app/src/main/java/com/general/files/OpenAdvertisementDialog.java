package com.general.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import com.buddyverse.providers.R;
import com.service.handler.ApiHandler;
import com.utils.LayoutDirection;
import com.utils.LoadImageGlide;
import com.utils.Logger;
import com.utils.Utils;

import java.util.HashMap;

public class OpenAdvertisementDialog {

    Context mContext;
    HashMap<String, String> data;
    GeneralFunctions generalFunc;

    androidx.appcompat.app.AlertDialog alertDialog;

    public OpenAdvertisementDialog(Context mContext, HashMap<String, String> data, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.data = data;
        this.generalFunc = generalFunc;

        show();
    }


    public void show() {
        androidx.appcompat.app.AlertDialog.Builder builder = new androidx.appcompat.app.AlertDialog.Builder(mContext, R.style.theme_advertise_dialog);
        builder.setTitle("");

        LayoutInflater inflater = (LayoutInflater) mContext.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        View dialogView = inflater.inflate(R.layout.advertisement_dailog, null);
        dialogView.setBackgroundColor(mContext.getResources().getColor(android.R.color.transparent));
        builder.setView(dialogView);

        ImageView bannerImage = (ImageView) dialogView.findViewById(R.id.bannerImage);
        View advBannerArea = dialogView.findViewById(R.id.advBannerArea);
        View mProgressBar = dialogView.findViewById(R.id.mProgressBar);

        String vImageWidth_ = data.get("vImageWidth");
        String vImageHeight_ = data.get("vImageHeight");

        if (vImageWidth_ != null && vImageHeight_ != null) {
            double vImageWidth = GeneralFunctions.parseIntegerValue((int) Utils.getScreenPixelWidth(mContext), vImageWidth_);
            double vImageHeight = GeneralFunctions.parseIntegerValue((int) Utils.getScreenPixelHeight(mContext), vImageHeight_);

            RelativeLayout.LayoutParams params = (RelativeLayout.LayoutParams) advBannerArea.getLayoutParams();
            params.width = (int) vImageWidth;
            params.height = (int) vImageHeight;

            advBannerArea.setLayoutParams(params);
        }

        String image_url = data.get("image_url");
        if (image_url != null && !image_url.equalsIgnoreCase("")) {
            mProgressBar.setVisibility(View.VISIBLE);


            new LoadImageGlide.builder(mContext, LoadImageGlide.bind(image_url), bannerImage).setGlideRequestBuilder(data.get("IS_GIF_IMAGE") != null && data.get("IS_GIF_IMAGE").equalsIgnoreCase("Yes") ? LoadImageGlide.GlideRequestBuilder.GIF : LoadImageGlide.GlideRequestBuilder.DRAWABLE).setGlideListener(new LoadImageGlide.GlideListener() {
                @Override
                public void onLoadFailed() {
                    mProgressBar.setVisibility(View.GONE);

                }

                @Override
                public void onResourceReady() {
                    mProgressBar.setVisibility(View.GONE);
                    HashMap<String, String> parameters = new HashMap<String, String>();
                    parameters.put("type", "insertBannereImpressionCount");
                    parameters.put("iAdvertBannerId", data.get("iAdvertBannerId"));
                    parameters.put("UserType", Utils.userType);
                    parameters.put("iUserId", generalFunc.getMemberId());

                    ApiHandler.execute(mContext, parameters, responseString -> {
                    });

                }
            }).build();

            /*Picasso.get()
                    .load(image_url)
                    .placeholder(R.mipmap.ic_no_icon)
                    .error(R.mipmap.ic_no_icon)
                    .into(bannerImage, new Callback() {
                        @Override
                        public void onSuccess() {
                            mProgressBar.setVisibility(View.GONE);
                        }

                        @Override
                        public void onError(Exception e) {
                            mProgressBar.setVisibility(View.GONE);
                        }
                    });*/
        }

        (dialogView.findViewById(R.id.cancelBtn)).setOnClickListener(view -> {
            if (alertDialog != null) {
                alertDialog.dismiss();
            }
        });

        (dialogView.findViewById(R.id.bannerImage)).setOnClickListener(view -> {
            try {
                String tRedirectUrl = data.get("tRedirectUrl");

                if (tRedirectUrl != null && !tRedirectUrl.equalsIgnoreCase("")) {
                    String redirect_url = tRedirectUrl;
                    (new ActUtils(mContext)).openURL(redirect_url);
                }

                if (alertDialog != null) {
                    alertDialog.dismiss();
                }
            } catch (Exception e) {

            }
        });


        alertDialog = builder.create();
        LayoutDirection.setLayoutDirection(alertDialog);
        try {
            alertDialog.getWindow().setBackgroundDrawableResource(android.R.color.transparent);
            alertDialog.show();
        } catch (Exception e) {
            Logger.e("Exception", "::" + e.getMessage());
        }
    }


}
