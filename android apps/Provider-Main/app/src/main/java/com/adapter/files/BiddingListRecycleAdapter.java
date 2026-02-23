package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.graphics.Color;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemBiddingLayoutBinding;
import com.utils.ServiceColor;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import java.util.Objects;

public class BiddingListRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1, TYPE_FOOTER = 2, TYPE_HEADER = 3;
    private final GeneralFunctions generalFunc;
    private OnItemClickListener mItemClickListener;
    private final ArrayList<HashMap<String, String>> list;
    private FooterViewHolder footerHolder;
    private boolean isFooterEnabled;
    private final String LBL_TASK_TXT, LBL_BIDDING_TASK_BUDGET_TXT, LBL_BIDDING_SERVICE_ADDRESS_TXT, LBL_VIEW_DETAILS, LBL_EARNED_AMOUNT;

    public BiddingListRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;

        LBL_TASK_TXT = generalFunc.retrieveLangLBl("", "LBL_TASK_TXT");
        LBL_BIDDING_TASK_BUDGET_TXT = generalFunc.retrieveLangLBl("", "LBL_BIDDING_BUDGET_TXT");
        LBL_EARNED_AMOUNT = generalFunc.retrieveLangLBl("", "LBL_EARNED_AMOUNT");
        LBL_BIDDING_SERVICE_ADDRESS_TXT = generalFunc.retrieveLangLBl("", "LBL_BIDDING_SERVICE_ADDRESS_TXT");
        LBL_VIEW_DETAILS = generalFunc.retrieveLangLBl("", "LBL_VIEW_DETAILS");
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {

        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false));
        } else if (viewType == TYPE_HEADER) {
            return new HeaderViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.earning_amount_layout, parent, false));
        } else {
            return new ViewHolder(ItemBiddingLayoutBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof final ViewHolder viewHolder) {
            final HashMap<String, String> item = list.get(position);

            String iActiveDisplay = item.get("bidding_status");

            viewHolder.binding.viewDetailsTxt.setText(LBL_VIEW_DETAILS);
            viewHolder.binding.historyNoHTxt.setText(LBL_TASK_TXT);
            if (Utils.checkText(iActiveDisplay) && iActiveDisplay.equalsIgnoreCase("Completed")) {
                viewHolder.binding.txtBiddingAmount.setText(LBL_EARNED_AMOUNT + ": " + generalFunc.convertNumberWithRTL(item.get("totalEarning")));
            } else {
                viewHolder.binding.txtBiddingAmount.setText(LBL_BIDDING_TASK_BUDGET_TXT + ": " + generalFunc.convertNumberWithRTL(item.get("fBiddingAmount")));
            }
            viewHolder.binding.historyNoVTxt.setText("#" + item.get("vBiddingPostNo"));
            /*String ConvertedTripRequestDate = item.get("ConvertedTripRequestDate");
            String ConvertedTripRequestTime = item.get("ConvertedTripRequestTime");
            if (ConvertedTripRequestDate != null) {
                viewHolder.binding.dateTxt.setText(ConvertedTripRequestDate);
                viewHolder.binding.timeTxt.setText(ConvertedTripRequestTime);
            }*/
            viewHolder.binding.dateTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.timeTxt.setText(item.get("tDisplayTime"));

            viewHolder.binding.sourceAddressTxt.setText(item.get("vServiceAddress"));
            viewHolder.binding.sourceAddressHTxt.setText(LBL_BIDDING_SERVICE_ADDRESS_TXT);

            String vServiceTitle = item.get("vTitle");
            viewHolder.binding.SelectedTypeNameTxt.setText(vServiceTitle);
            viewHolder.binding.typeArea.setCardBackgroundColor(ServiceColor.BIDDING.color);
            viewHolder.binding.SelectedTypeNameTxt.setTextColor(Color.parseColor(ServiceColor.UI_TEXT_COLORS[0]));

            if (Utils.checkText(iActiveDisplay)) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.statusVTxt.setText(iActiveDisplay);
            } else {
                viewHolder.binding.statusArea.setVisibility(View.GONE);
            }

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.statusArea.setRotation(180);
                viewHolder.binding.statusVTxt.setRotation(180);
            }

            viewHolder.binding.SelectedTypeNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.SelectedTypeNameTxt.setSelected(true);
            viewHolder.binding.SelectedTypeNameTxt.setSingleLine(true);

            viewHolder.binding.statusVTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.statusVTxt.setSelected(true);
            viewHolder.binding.statusVTxt.setSingleLine(true);

            if (Objects.requireNonNull(item.get("showDetailBtn")).equalsIgnoreCase("Yes")) {
                viewHolder.binding.viewDetailsArea.setVisibility(View.VISIBLE);
                viewHolder.binding.viewDetailsArea.setOnClickListener(view -> btnClicked(view, position, "ViewDetail"));
            } else {
                viewHolder.binding.viewDetailsArea.setVisibility(View.GONE);
            }

        } else if (holder instanceof HeaderViewHolder headerHolder) {
            Map<String, String> map = list.get(position);

            headerHolder.tripsCountTxt.setText(map.get("TripCount"));
            headerHolder.fareTxt.setText(map.get("TotalEarning"));
            headerHolder.avgRatingCalcTxt.setText(map.get("AvgRating"));

            if (map.containsKey("isCalenderView") && Objects.requireNonNull(map.get("isCalenderView")).equalsIgnoreCase("yes")) {
                headerHolder.earnedAmountArea.setVisibility(View.VISIBLE);
            } else {
                headerHolder.earnedAmountArea.setVisibility(View.GONE);
            }

        } else {
            this.footerHolder = (FooterViewHolder) holder;
        }
    }

    private void btnClicked(View view, int position, String type) {
        if (mItemClickListener != null) {
            mItemClickListener.onItemClickList(view, position, type);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if ((position) == 0) {
            return TYPE_HEADER;
        }
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled) {
            return list.size() + 1;
        } else {
            return list.size();
        }
    }

    @SuppressLint("NotifyDataSetChanged")
    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            isFooterEnabled = false;
            footerHolder.progressArea.setVisibility(View.GONE);
        }
    }

    public interface OnItemClickListener {
        void onItemClickList(View v, int position, String type);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemBiddingLayoutBinding binding;

        private ViewHolder(ItemBiddingLayoutBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressArea = (LinearLayout) itemView;
        }
    }

    private class HeaderViewHolder extends RecyclerView.ViewHolder {

        private final LinearLayout earnedAmountArea;
        private final MTextView earnTitleTxt, fareTxt, tripsCompletedTxt, tripsCountTxt, avgRatingTxt, avgRatingCalcTxt;

        private HeaderViewHolder(View view) {
            super(view);
            earnTitleTxt = view.findViewById(R.id.earnTitleTxt);
            fareTxt = view.findViewById(R.id.fareTxt);
            tripsCompletedTxt = view.findViewById(R.id.tripsCompletedTxt);
            tripsCountTxt = view.findViewById(R.id.tripsCountTxt);
            avgRatingTxt = view.findViewById(R.id.avgRatingTxt);
            avgRatingCalcTxt = view.findViewById(R.id.avgRatingCalcTxt);
            earnedAmountArea = view.findViewById(R.id.earnedAmountArea);

            earnTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_EARNINGS"));
            tripsCompletedTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_BIDS_TXT"));
            avgRatingTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AVG_RATING"));
        }
    }
}