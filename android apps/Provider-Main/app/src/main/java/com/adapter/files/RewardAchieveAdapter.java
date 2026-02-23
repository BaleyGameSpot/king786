package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.Objects;

public class RewardAchieveAdapter extends RecyclerView.Adapter<RewardAchieveAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    private JSONArray itemDataArr;
    Context mContext;

    public RewardAchieveAdapter(Context mContext, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.generalFunc = generalFunc;
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_reward_view, parent, false);
        return new ViewHolder(view);
    }

    @SuppressLint("UseCompatLoadingForColorStateLists")
    @Override
    public void onBindViewHolder(@NotNull ViewHolder holder, final int position) {
        JSONObject itemObject = generalFunc.getJsonObject(itemDataArr, position);


        if (generalFunc.getJsonValueStr("status", itemObject).equalsIgnoreCase("1")) {
            JSONObject dataView = generalFunc.getJsonObject(generalFunc.getJsonValueStr("data", itemObject));
            JSONArray rewardDetails = generalFunc.getJsonArray(generalFunc.getJsonValueStr("reward_details", dataView));
            holder.txtNote.setVisibility(View.VISIBLE);
            holder.txtRewardTitle.setText(generalFunc.getJsonValueStr("vTitle", dataView));
            holder.txtNote.setText(generalFunc.getJsonValueStr("REWARD_AMOUNT_TEXT", dataView));
            if (generalFunc.isRTLmode()) {
                holder.checkArea.setBackground(ContextCompat.getDrawable(mContext, R.drawable.drawable_rounded_left_curve));
            }
            holder.checkArea.setVisibility(View.VISIBLE);

            if (rewardDetails != null) {
                for (int j = 0; j < rewardDetails.length(); j++) {

                    LayoutInflater infalInflater = (LayoutInflater) holder.itemView.getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                    @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.design_reward_detail_row, null);

                    MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
                    MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);
                    MTextView rewardStatus = (MTextView) convertView.findViewById(R.id.rewardStatus);
                    LinearLayout ll_detail_row = (LinearLayout) convertView.findViewById(R.id.ll_reward_detail_row);


                    JSONObject jobject = generalFunc.getJsonObject(rewardDetails, j);

                    titleHTxt.setText(generalFunc.getJsonValueStr("vTitle", jobject));
                    rewardStatus.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("TEXT", jobject)));
                    titleVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vValue", jobject)));
                    if (generalFunc.getJsonValueStr("is_completed", jobject).equalsIgnoreCase("Yes")) {
                        rewardStatus.setTextColor(mContext.getResources().getColorStateList(R.color.green));
                        ll_detail_row.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.green));
                    } else {
                        ll_detail_row.setBackgroundTintList(mContext.getResources().getColorStateList(R.color.red));
                        rewardStatus.setTextColor(mContext.getResources().getColorStateList(R.color.red));
                    }
                    if (!jobject.has("content") && !jobject.has("AchivedTitle")) {
                        holder.llRewardList.addView(convertView);
                    }
                }
            }
        } else {

            JSONArray levelCriteria = generalFunc.getJsonArray(generalFunc.getJsonValueStr("level_criteria", itemObject));

            holder.checkArea.setVisibility(View.GONE);
            holder.txtNote.setVisibility(View.GONE);

            for (int jk = 0; jk < levelCriteria.length(); jk++) {

                JSONObject jobject = generalFunc.getJsonObject(levelCriteria, jk);

                try {
                    LayoutInflater infalInflater = (LayoutInflater) holder.itemView.getContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
                    @SuppressLint("InflateParams") View convertView = infalInflater.inflate(R.layout.design_reward_list_row, null);

                    MTextView titleHTxt = (MTextView) convertView.findViewById(R.id.titleHTxt);
                    MTextView titleVTxt = (MTextView) convertView.findViewById(R.id.titleVTxt);
                    MTextView tvLblCompleteMsg = (MTextView) convertView.findViewById(R.id.tvLblCompleteMsg);

                    String dataKey = Objects.requireNonNull(jobject.names()).getString(0);
                    if (dataKey != null) {
                        if (jk == 0) {
                            holder.txtRewardTitle.setText(jobject.get(dataKey).toString());
                        } else {
                            titleHTxt.setText(generalFunc.getJsonValueStr("vTitle", jobject));
                            titleVTxt.setText(generalFunc.convertNumberWithRTL(generalFunc.getJsonValueStr("vValue", jobject)));
                            String content = generalFunc.getJsonValueStr("content", jobject);
                            if (Utils.checkText(content)) {
                                titleHTxt.setVisibility(View.GONE);
                                titleVTxt.setVisibility(View.GONE);
                                tvLblCompleteMsg.setText(content);
                                tvLblCompleteMsg.setVisibility(View.VISIBLE);
                            } else {
                                tvLblCompleteMsg.setVisibility(View.GONE);
                            }
                            holder.llRewardList.addView(convertView);
                        }
                    }
                } catch (JSONException e) {
                    Logger.e("Exception","::"+e.getMessage());
                }
            }
        }
    }

    @Override
    public int getItemCount() {
        return itemDataArr.length();
    }

    public void updateList(JSONArray rewardsToAchieveArr) {
        this.itemDataArr = rewardsToAchieveArr;
        notifyDataSetChanged();
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        private final LinearLayout llRewardList;
        private final LinearLayout checkArea;
        private final MTextView txtRewardTitle;
        private final MTextView txtNote;

        public ViewHolder(View itemView) {
            super(itemView);
            checkArea = (LinearLayout) itemView.findViewById(R.id.checkArea);
            txtRewardTitle = (MTextView) itemView.findViewById(R.id.txtRewardTitle);
            llRewardList = (LinearLayout) itemView.findViewById(R.id.llRewardList);
            txtNote = (MTextView) itemView.findViewById(R.id.txtNote);
        }
    }
}