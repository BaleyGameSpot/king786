package com.adapter.files;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FooterListBinding;
import com.buddyverse.main.databinding.ItemNotificationViewBinding;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

public class NotificationAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    @Nullable
    private final ArrayList<HashMap<String, String>> list;
    @Nullable
    private final OnItemClickListener mItemClickListener;
    boolean isFooterEnabled = false;

    private FooterViewHolder footerHolder;
    private final int topMargin, topMargin1, maxHeight, minHeight;
    private final String readMoreLbl;

    public NotificationAdapter(@NonNull Context mContext, @NonNull GeneralFunctions generalFunc, @Nullable ArrayList<HashMap<String, String>> list, @Nullable OnItemClickListener mItemClickListener) {
        this.list = list;
        this.mItemClickListener = mItemClickListener;

        this.topMargin = (int) mContext.getResources().getDimension(R.dimen._15sdp);
        this.topMargin1 = (int) mContext.getResources().getDimension(R.dimen._10sdp);
        this.maxHeight = (int) mContext.getResources().getDimension(R.dimen._110sdp);
        this.minHeight = (int) mContext.getResources().getDimension(R.dimen._70sdp);

        this.readMoreLbl = generalFunc.retrieveLangLBl("", "LBL_READ_MORE");
    }

    @NonNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_FOOTER) {
            return new FooterViewHolder(FooterListBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ViewHolder(ItemNotificationViewBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        if (holder instanceof ViewHolder vHolder) {
            assert list != null;
            HashMap<String, String> map = list.get(position);

            String vTitle = map.get("vTitle");
            if (Utils.checkText(vTitle)) {
                vHolder.binding.titleTxt.setText(vTitle);
                vHolder.binding.titleTxt.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.titleTxt.setVisibility(View.GONE);

                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) vHolder.binding.detailsTxt.getLayoutParams();
                params.setMargins(0, topMargin, 0, 0);
                vHolder.binding.detailsTxt.setLayoutParams(params);
            }

            String tDescription = map.get("tDescription");
            if (Utils.checkText(tDescription)) {
                vHolder.binding.detailsTxt.setText(tDescription);
                vHolder.binding.detailsTxt.setVisibility(View.VISIBLE);
            } else {
                vHolder.binding.detailsTxt.setVisibility(View.GONE);
            }

            if (vHolder.binding.titleTxt.getVisibility() == View.VISIBLE && vHolder.binding.detailsTxt.getVisibility() == View.VISIBLE) {
                LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) vHolder.binding.titleTxt.getLayoutParams();
                params.setMargins(0, topMargin1, 0, 0);
                vHolder.binding.titleTxt.setLayoutParams(params);
                vHolder.binding.cardArea.setMinimumHeight(maxHeight);
            } else {
                vHolder.binding.cardArea.setMinimumHeight(minHeight);
            }

            /*String dDateTime = map.get("dDateTime");
            if (Utils.checkText(dDateTime)) {
                vHolder.binding.dateTxt.setText(dDateTime);
            }*/
            if (map.containsKey("tDisplayDate") && Utils.checkText(map.get("tDisplayDate"))){
                vHolder.binding.dateTxt.setText(map.get("tDisplayDate"));
            }
            if (map.containsKey("tDisplayTime") && Utils.checkText(map.get("tDisplayTime"))){
                vHolder.binding.timeTxt.setText(map.get("tDisplayTime"));
            }

            vHolder.binding.readMoreTxt.setText(readMoreLbl);
            vHolder.binding.readMoreTxt.setOnClickListener(view -> {
                if (mItemClickListener != null) {
                    mItemClickListener.onReadMoreItemClick(map);
                }
            });

        } else if (holder instanceof FooterViewHolder footerViewHolder) {
            this.footerHolder = footerViewHolder;
        }
    }

    @Override
    public int getItemViewType(int position) {
        assert list != null;
        if (position == list.size() && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    @Override
    public int getItemCount() {
        return list != null ? isFooterEnabled ? list.size() + 1 : list.size() : 0;
    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.VISIBLE);
        }
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            footerHolder.binding.progressContainer.setVisibility(View.GONE);
        }
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemNotificationViewBinding binding;

        private ViewHolder(ItemNotificationViewBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class FooterViewHolder extends RecyclerView.ViewHolder {
        private final FooterListBinding binding;

        private FooterViewHolder(FooterListBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnItemClickListener {
        void onReadMoreItemClick(@NonNull HashMap<String, String> map);
    }
}
