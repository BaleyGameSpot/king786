package com.adapter.files;

import android.annotation.SuppressLint;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemSubscriptionBinding;
import com.buddyverse.providers.databinding.ItemSubscriptionHistoryBinding;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class SubscriptionAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1, TYPE_HISTORY = 2, TYPE_FOOTER = 3;
    private final ArrayList<HashMap<String, String>> list;
    private OnItemClickListener mItemClickListener;
    private boolean isFooterEnabled = false;
    private FooterViewHolder footerHolder;
    private final String type; // "","Details", "history"

    public SubscriptionAdapter(ArrayList<HashMap<String, String>> list, String type) {
        this.list = list;
        this.type = type;
    }

    public void setOnItemClickListener(SubscriptionAdapter.OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        if (viewType == TYPE_HISTORY) {
            return new HistoryViewHolder(ItemSubscriptionHistoryBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false));
        } else {
            return new ViewHolder(ItemSubscriptionBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull final RecyclerView.ViewHolder holder, final int position) {

        if (holder instanceof ViewHolder viewHolder) {

            HashMap<String, String> item = list.get(position);

            viewHolder.binding.subscriptionTxt.setText(item.get("vPlanName"));
            viewHolder.binding.subscriptionNameTxt.setText("(" + item.get("PlanDuration") + ")");
            viewHolder.binding.subscriptionDescTxt.setText(item.get("vPlanDescription"));
            viewHolder.binding.subscriptionPriceTxt.setText(item.get("fPlanPrice"));

            if (Objects.requireNonNull(item.get("isRenew")).equalsIgnoreCase("Yes")) {
                viewHolder.binding.subScribeBtnTxt.setText(item.get("renewLBL"));
                viewHolder.binding.cancelBtnTxt.setText(item.get("eSubscriptionStatusLbl"));
            } else {
                viewHolder.binding.subScribeBtnTxt.setText(item.get("eSubscriptionStatusLbl"));
            }


            if (Objects.requireNonNull(item.get("showPlanDetails")).equalsIgnoreCase("Yes")) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.cancelBtnTxt.setVisibility(Objects.requireNonNull(item.get("isRenew")).equalsIgnoreCase("Yes") ? View.VISIBLE : View.GONE);

                viewHolder.binding.statusTxt.setText(item.get("statusLbl") + ": ");
                viewHolder.binding.subscribedStatus.setText(item.get("eSubscriptionDetailStatusLbl"));

                viewHolder.binding.planDetailsArea.setVisibility(View.VISIBLE);
                viewHolder.binding.tvSubscribOnDateTxt.setText(item.get("subscribedOnLBL") + ": ");
                viewHolder.binding.tvSubscribOnDateValTxt.setText(item.get("tSubscribeDate"));
                viewHolder.binding.tvExpireOnDateTxt.setText(item.get("expiredOnLBL") + ": ");
                viewHolder.binding.tvExpireOnDateValTxt.setText(item.get("tExpiryDate"));

            } else {
                viewHolder.binding.cancelBtnTxt.setVisibility(View.GONE);
                viewHolder.binding.statusArea.setVisibility(View.GONE);
                viewHolder.binding.planDetailsArea.setVisibility(View.GONE);
            }

            viewHolder.binding.subScribeBtnTxt.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onSubscribeItemClick(view, position, "Renew");
                }
            });

            viewHolder.binding.cancelBtnTxt.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onSubscribeItemClick(view, position, "Cancel");
                }
            });


        } else if (holder instanceof HistoryViewHolder viewHolder) {

            final HashMap<String, String> item = list.get(position);

            viewHolder.binding.tvPlanTypeTitleTxt.setText(item.get("planTypeLBL"));
            viewHolder.binding.tvPlanTypeValTxt.setText(item.get("vPlanName"));
            viewHolder.binding.tvPlanDurationTitleTxt.setText(item.get("planDurationLBL"));
            viewHolder.binding.tvPlanDurationValTxt.setText(item.get("PlanDuration"));
            viewHolder.binding.tvPlanPriceTitleTxt.setText(item.get("planPriceLBL"));
            viewHolder.binding.tvPlanPriceValTxt.setText(item.get("fPlanPrice"));
            viewHolder.binding.tvSubscribOnDateTxt.setText(item.get("subscribedOnLBL"));
            viewHolder.binding.tvSubscribOnDateValTxt.setText(item.get("tSubscribeDate"));
            viewHolder.binding.tvExpireOnDateTxt.setText(item.get("expiredOnLBL"));
            viewHolder.binding.tvExpireOnDateValTxt.setText(item.get("tExpiryDate"));
            viewHolder.binding.subscribedStatusTitle.setText(item.get("subscribedStatusLbl"));
            viewHolder.binding.subscribedStatus.setText(item.get("eSubscriptionStatusLbl"));

            String planLeftDays = item.get("planLeftDays");

            if (Objects.requireNonNull(item.get("eSubscriptionStatus")).equalsIgnoreCase("Subscribed") && !TextUtils.isEmpty(planLeftDays)) {
                viewHolder.binding.packageArea.setVisibility(View.VISIBLE);
                viewHolder.binding.tvLeftPackageDays.setText(item.get("FormattedPlanLeftDays"));
                viewHolder.binding.tvDaysTitle.setText(item.get("planLeftDaysTitle1"));
                viewHolder.binding.tvDaysTitle2.setText(item.get("planLeftDaysTitle2"));
            } else {
                viewHolder.binding.packageArea.setVisibility(View.GONE);
            }

            viewHolder.binding.tvPackageDetail.setText(item.get("PlanDuration"));

        } else {
            this.footerHolder = (FooterViewHolder) holder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        } else if (type.equalsIgnoreCase("History") || type.equalsIgnoreCase("Details")) {
            return TYPE_HISTORY;
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
        if (footerHolder != null) {
            footerHolder.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressContainer.setVisibility(View.GONE);
    }

    protected static class ViewHolder extends RecyclerView.ViewHolder {

        private final ItemSubscriptionBinding binding;

        private ViewHolder(ItemSubscriptionBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class HistoryViewHolder extends RecyclerView.ViewHolder {

        private final ItemSubscriptionHistoryBinding binding;

        private HistoryViewHolder(ItemSubscriptionHistoryBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    protected static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final LinearLayout progressContainer;

        public FooterViewHolder(View itemView) {
            super(itemView);
            progressContainer = (LinearLayout) itemView.findViewById(R.id.progressContainer);
        }
    }

    public interface OnItemClickListener {
        void onSubscribeItemClick(View v, int position, String planStatus);
    }
}