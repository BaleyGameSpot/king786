package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.like.LikeButton;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.MButton;
import com.view.MTextView;
import com.view.MaterialRippleLayout;
import com.view.SelectableRoundedImageView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;

public class UberXOnlineDriverListAdapter extends RecyclerView.Adapter<UberXOnlineDriverListAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    private final Context mContext;
    private OnItemClickListener mItemClickListener;
    private final String LANGUAGE_IS_RTL_KEY, ENABLE_FAVORITE_DRIVER_MODULE;

    public UberXOnlineDriverListAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.LANGUAGE_IS_RTL_KEY = generalFunc.retrieveValue(Utils.LANGUAGE_IS_RTL_KEY);
        this.ENABLE_FAVORITE_DRIVER_MODULE = generalFunc.retrieveValue(Utils.ENABLE_FAVORITE_DRIVER_MODULE_KEY);
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @NotNull
    @Override
    public UberXOnlineDriverListAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new ViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_online_driver_list_design, parent, false));
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(final @NotNull ViewHolder viewHolder, final int position) {
        HashMap<String, String> item = list.get(position);

        String image_url = CommonUtilities.PROVIDER_PHOTO_PATH + item.get("driver_id") + "/" + item.get("driver_img");

        new LoadImage.builder(LoadImage.bind(image_url), viewHolder.driverImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();

        String fAmount = item.get("fAmount");

        if (generalFunc.isRTLmode()) {
            viewHolder.btnImg.setRotation(180);
            viewHolder.btnArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.login_border_rtl));
            viewHolder.labelFeatured.setBackground(ContextCompat.getDrawable(mContext, R.drawable.right_radius_rtl_1));
        }

        if (fAmount != null && !fAmount.trim().equals("") && !fAmount.trim().equals("0")) {
            viewHolder.priceTxt.setText(generalFunc.convertNumberWithRTL(fAmount));
        } else {
            viewHolder.priceTxt.setVisibility(View.GONE);
        }
        String IS_PROVIDER_ONLINE = item.get("IS_PROVIDER_ONLINE");
        if (IS_PROVIDER_ONLINE != null && IS_PROVIDER_ONLINE.equalsIgnoreCase("Yes")) {
            viewHolder.driverStatus.setColorFilter(ContextCompat.getColor(mContext, R.color.Green));
        } else {
            viewHolder.driverStatus.setColorFilter(ContextCompat.getColor(mContext, R.color.Red));
        }


        String LBL_FEATURED_TXT = generalFunc.retrieveLangLBl("Featured", "LBL_FEATURED_TXT");

        if (item.get("eIsFeatured").equalsIgnoreCase("Yes")) {
            viewHolder.labelFeatured.setText(LBL_FEATURED_TXT);
            viewHolder.labelFeatured.setVisibility(View.VISIBLE);

        } else if (item.get("eIsFeatured").equalsIgnoreCase("No")) {
            viewHolder.labelFeatured.setVisibility(View.GONE);

            // viewHolder.cardArea.setBackgroundResource(0);
        }

        // viewHolder.ratingBar.setRating(generalFunc.parseFloatValue(0, item.get("average_rating")));
        viewHolder.driverNameTxt.setText(item.get("Name") + " " + item.get("LastName"));
        viewHolder.rateTxt.setText(item.get("average_rating"));
        if (item.get("average_rating").equalsIgnoreCase("")){
            viewHolder.Rating.setVisibility(View.GONE);
        }
        viewHolder.btn_type2.setText(item.get("LBL_SEND_REQUEST"));
        viewHolder.btnTxt.setText(item.get("LBL_MORE_INFO_TXT"));
        viewHolder.milesTxt.setText(generalFunc.convertNumberWithRTL(item.get("DIST_TO_PICKUP_INT_ROW")));

        if (item.get("eVideoConsultEnable") != null && item.get("eVideoConsultEnable").equalsIgnoreCase("Yes")) {

            viewHolder.priceTxt.setVisibility(View.GONE);
            viewHolder.videoConuslatArea.setVisibility(View.VISIBLE);
            viewHolder.videoValTxt.setText(generalFunc.convertNumberWithRTL(fAmount));
            viewHolder.videoHTxt.setText(item.get("VideoConsultServiceChargeTitle"));
            viewHolder.milesTxt.setVisibility(View.GONE);
        } else {
            viewHolder.videoConuslatArea.setVisibility(View.GONE);
        }

        if (ENABLE_FAVORITE_DRIVER_MODULE.equalsIgnoreCase("Yes") && item.get("eFavDriver").equalsIgnoreCase("Yes")) {
            viewHolder.likeButton.setVisibility(View.VISIBLE);
            viewHolder.likeButton.setLiked(true);
            viewHolder.likeButton.setEnabled(false);
        } else {
            viewHolder.likeButton.setVisibility(View.GONE);
        }

        viewHolder.btnArea.setOnClickListener(view -> {
            if (mItemClickListener != null) {
                mItemClickListener.onItemClickList(view, position);
            }
        });
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public static class ViewHolder extends RecyclerView.ViewHolder {

        private final SelectableRoundedImageView driverImgView;
        private final MTextView driverNameTxt, priceTxt, milesTxt, labelFeatured, btnTxt, videoHTxt, videoValTxt,rateTxt;
        private final MButton btn_type2;
        private final LinearLayout btnArea,Rating;
        // private final SimpleRatingBar ratingBar;
        private final ImageView btnImg, driverStatus;
        private final LikeButton likeButton;
        private final View videoConuslatArea;

        public ViewHolder(View view) {
            super(view);

            driverImgView = view.findViewById(R.id.driverImgView);
            driverNameTxt = view.findViewById(R.id.driverNameTxt);
            // ratingBar = view.findViewById(R.id.ratingBar);
            priceTxt = view.findViewById(R.id.priceTxt);
            milesTxt = view.findViewById(R.id.milesTxt);
            btn_type2 = ((MaterialRippleLayout) view.findViewById(R.id.btn_request)).getChildView();
            labelFeatured = view.findViewById(R.id.labelFeatured);
            btnTxt = view.findViewById(R.id.btnTxt);
            btnImg = view.findViewById(R.id.btnImg);
            btnArea = view.findViewById(R.id.btnArea);
            driverStatus = view.findViewById(R.id.driverStatus);
            likeButton = view.findViewById(R.id.likeButton);
            videoConuslatArea = view.findViewById(R.id.videoConuslatArea);
            videoHTxt = view.findViewById(R.id.videoHTxt);
            videoValTxt = view.findViewById(R.id.videoValTxt);
            rateTxt = view.findViewById(R.id.rateTxt);
            Rating = view.findViewById(R.id.Rating);
        }
    }
}