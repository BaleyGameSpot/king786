package com.act.rideSharingPro.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.databinding.FooterListBinding;
import com.buddyverse.main.databinding.ItemRideMyListBinding;
import com.model.ServiceModule;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RideMyPublishAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {
    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    private final GeneralFunctions generalFunc;
    private final ArrayList<HashMap<String, String>> list;
    private final OnClickListener onClickListener;
    private boolean isFooterEnabled = false;
    private FooterViewHolder footerHolder;

    public RideMyPublishAdapter(GeneralFunctions generalFunc, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.generalFunc = generalFunc;
        this.list = list;
        this.onClickListener = listener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(FooterListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new DataViewHolder(ItemRideMyListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {

        if (holder instanceof final DataViewHolder viewHolder) {
            HashMap<String, String> mapData = list.get(position);

            if ((mapData.containsKey("Btn_StartTrip") && Objects.requireNonNull(mapData.get("Btn_StartTrip")).equalsIgnoreCase("Yes"))
                    || (mapData.containsKey("Btn_TripDetails") && Objects.requireNonNull(mapData.get("Btn_TripDetails")).equalsIgnoreCase("Yes"))) {
                viewHolder.binding.startTripBtnArea.setVisibility(View.VISIBLE);
                MButton startTripBtn = ((MaterialRippleLayout) viewHolder.binding.startTripBtn).getChildView();
                startTripBtn.setText(mapData.get(generalFunc.retrieveLangLBl("", "BTN_TEXT")));
                viewHolder.binding.startTripBtn.setOnClickListener(view -> onClickListener.onStartTripButtonCall(position, mapData));
            } else {
                viewHolder.binding.startTripBtnArea.setVisibility(View.GONE);
            }


            viewHolder.binding.bookNoTxt.setText(mapData.get("vPublishedRideNoTxt"));
//        if (mapData.containsKey("eStatusText")) {
//            viewHolder.binding.statusVTxt.setText(mapData.get("eStatusText"));
//        }

            if (ServiceModule.EnableRideSharingPro && Utils.checkText(mapData.get("pendingReqMsg"))) {
                viewHolder.binding.pendingReqMsgTxt.setVisibility(View.VISIBLE);
                viewHolder.binding.pendingReqMsgTxt.setText(mapData.get("pendingReqMsg"));
            } else {
                viewHolder.binding.pendingReqMsgTxt.setVisibility(View.GONE);
            }

            if (mapData.containsKey("USERS_AVG_RATING_FOR_RIDE") && Utils.checkText(mapData.get("USERS_AVG_RATING_FOR_RIDE")) && !mapData.get("USERS_AVG_RATING_FOR_RIDE").equalsIgnoreCase("0") && !mapData.get("USERS_AVG_RATING_FOR_RIDE").equalsIgnoreCase("0.0")) {
                viewHolder.binding.ratingAreaLayout.setVisibility(View.VISIBLE);
                viewHolder.binding.ratingValue.setText(mapData.get("USERS_AVG_RATING_FOR_RIDE"));
            }

            viewHolder.binding.startCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_START_LOC_TXT"));
            viewHolder.binding.startAddressTxt.setText(mapData.get("tStartLocation"));
            viewHolder.binding.startTimeTxt.setText(mapData.get("StartTime"));

            viewHolder.binding.endCityTxt.setText(generalFunc.retrieveLangLBl("", "LBL_RIDE_SHARE_DETAILS_END_LOC_TXT"));
            viewHolder.binding.endAddressTxt.setText(mapData.get("tEndLocation"));
            viewHolder.binding.endTimeTxt.setText(mapData.get("EndTime"));

            //viewHolder.binding.dateTxt.setText(mapData.get("StartDate"));
            viewHolder.binding.dateTxt.setText(mapData.get("tDisplayDate"));
            viewHolder.binding.priceTxt.setText(mapData.get("fPrice"));
            viewHolder.binding.priceMsgTxt.setText(mapData.get("PriceLabel"));
            if (mapData.containsKey("eApproveDoc") && Objects.requireNonNull(mapData.get("eApproveDoc")).equalsIgnoreCase("No")) {
                viewHolder.binding.verifyDocStatusTxt.setVisibility(View.VISIBLE);
                viewHolder.binding.verifyDocStatusTxt.setText(mapData.get("DocReviewMes"));
            } else {
                viewHolder.binding.verifyDocStatusTxt.setVisibility(View.GONE);
            }
            viewHolder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));
        } else if (holder instanceof final FooterViewHolder footerViewHolder) {
            this.footerHolder = footerViewHolder;
        }
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.binding.progressContainer.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            isFooterEnabled = false;
            footerHolder.binding.progressContainer.setVisibility(View.GONE);
        }
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemRideMyListBinding binding;

        private DataViewHolder(@NonNull ItemRideMyListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final FooterListBinding binding;

        private FooterViewHolder(@NonNull FooterListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, HashMap<String, String> mapData);

        void onStartTripButtonCall(int position, HashMap<String, String> mapData);
    }
}