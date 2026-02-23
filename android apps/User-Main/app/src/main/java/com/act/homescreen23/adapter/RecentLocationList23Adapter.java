package com.act.homescreen23.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.act.UberXHomeActivity;
import com.buddyverse.main.databinding.Item23RecentLocationItemBinding;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class RecentLocationList23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final UberXHomeActivity mActivity;
    private final GeneralFunctions generalFunc;
    private final OnClickListener listener;
    private JSONArray mServicesArr;
    private final JSONObject layoutDetailsObj;

    public RecentLocationList23Adapter(@NonNull UberXHomeActivity activity, @NonNull GeneralFunctions generalFunctions, @NonNull JSONObject itemObject, @NonNull OnClickListener listener) {
        this.mActivity = activity;
        this.generalFunc = generalFunctions;
        this.mServicesArr = mActivity.generalFunc.getJsonArray("DestinationLocations", itemObject);
        this.layoutDetailsObj = mActivity.generalFunc.getJsonObject("LayoutDetails", itemObject);
        this.listener = listener;
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(Item23RecentLocationItemBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        ViewHolder bHolder = (ViewHolder) holder;

        JSONObject mServiceObject = generalFunc.getJsonObject(mServicesArr, position);

        String vTitle = generalFunc.getJsonValueStr("tDaddressMain", mServiceObject);
        String vTitleFont = generalFunc.getJsonValueStr("vTitleFont", layoutDetailsObj);
        String vTxtTitleColor = generalFunc.getJsonValueStr("vTxtTitleColor", layoutDetailsObj);
        HomeUtils.manageTextView(mActivity, bHolder.binding.recentLocationTxt, vTitle, vTitleFont, vTxtTitleColor);

        String vSubtitle = generalFunc.getJsonValueStr("tDaddressSub", mServiceObject);
        String vSubTitleFont = generalFunc.getJsonValueStr("vSubTitleFont", layoutDetailsObj);
        String vTxtSubTitleColor = generalFunc.getJsonValueStr("vTxtSubTitleColor", layoutDetailsObj);
        HomeUtils.manageTextView(mActivity, bHolder.binding.recentLocationSubTxt, vSubtitle, vSubTitleFont, vTxtSubTitleColor);

        ///////////////////////////////////////////////////////////////-----------------------------
        bHolder.binding.contentArea.setOnClickListener(v -> listener.onRecentLocationItemClick(position, mServiceObject));
    }

    @Override
    public int getItemCount() {
        if (mServicesArr == null) {
            return 0;
        }
        return mServicesArr.length();
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray servicesArr) {
        this.mServicesArr = servicesArr;
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final Item23RecentLocationItemBinding binding;

        private ViewHolder(Item23RecentLocationItemBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onRecentLocationItemClick(int morePos, JSONObject mServiceObject);
    }
}