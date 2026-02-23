package com.act;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.databinding.DataBindingUtil;
import androidx.recyclerview.widget.RecyclerView;

import com.activity.ParentActivity;
import com.general.files.ActUtils;
import com.general.files.GeneralFunctions;
import com.general.files.KmRecyclerView;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ActivityHelp23ProBinding;
import com.buddyverse.main.databinding.ItemDesignCategoryHelpBinding;
import com.buddyverse.main.databinding.ItemDesignCategoryHelpTitleBinding;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.HashMap;

public class HelpActivity23Pro extends ParentActivity {

    private ActivityHelp23ProBinding binding;

    private HelpMainAdapter mHelpMainAdapter;
    @Nullable
    private JSONArray jsonArray;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        binding = DataBindingUtil.setContentView(this, R.layout.activity_help23_pro);

        initViews();
        getHelpList();
    }

    private void initViews() {
        ImageView backImgView = findViewById(R.id.backImgView);
        if (generalFunc.isRTLmode()) {
            backImgView.setRotation(180);
        }
        addToClickHandler(backImgView);
        MTextView titleTxt = findViewById(R.id.titleTxt);
        titleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_FAQ_TXT"));

        //
        mHelpMainAdapter = new HelpMainAdapter();
        binding.rvHelp.setAdapter(mHelpMainAdapter);
    }

    private Context getActContext() {
        return HelpActivity23Pro.this;
    }

    @SuppressLint("NotifyDataSetChanged")
    private void getHelpList() {
        binding.contentView.setVisibility(View.GONE);
        binding.noHelpTxt.setVisibility(View.GONE);
        binding.loadingBar.setVisibility(View.VISIBLE);

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getFAQ");
        parameters.put("iMemberId", generalFunc.getMemberId());
        parameters.put("appType", Utils.app_type);

        ApiHandler.execute(getActContext(), parameters, responseString -> {

            binding.loadingBar.setVisibility(View.GONE);

            if (Utils.checkText(responseString)) {
                if (GeneralFunctions.checkDataAvail(Utils.action_str, responseString)) {

                    JSONArray carList_arr = generalFunc.getJsonArray(Utils.message_str, responseString);
                    if (carList_arr != null) {
                        binding.contentView.setVisibility(View.VISIBLE);

                        this.jsonArray = carList_arr;
                        mHelpMainAdapter.notifyDataSetChanged();
                    }
                } else {
                    binding.noHelpTxt.setText(generalFunc.retrieveLangLBl("", generalFunc.getJsonValue(Utils.message_str, responseString)));
                    binding.noHelpTxt.setVisibility(View.VISIBLE);
                }
            } else {
                generalFunc.showError(true);
            }
        });
    }

    @SuppressLint({"SetTextI18n", "InflateParams"})
    private void HelpServices(JSONObject jsonObject, LinearLayout serviceSelectArea, boolean isLast) {
        final LayoutInflater inflater = (LayoutInflater) getActContext().getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        @NonNull ItemDesignCategoryHelpBinding binding2 = ItemDesignCategoryHelpBinding.inflate(inflater, serviceSelectArea, false);

        binding2.detailsTxt.setText(generalFunc.getJsonValueStr("vTitle", jsonObject));
        binding2.layoutBackground.setOnClickListener(view -> {
            Bundle bn = new Bundle();
            bn.putString("QUESTION", generalFunc.getJsonValueStr("vTitle", jsonObject));
            bn.putString("ANSWER", generalFunc.getJsonValueStr("tAnswer", jsonObject));
            new ActUtils(getActContext()).startActWithData(QuestionAnswerActivity.class, bn);
        });

        if (generalFunc.isRTLmode()) {
            binding2.imageArrow.setRotation(90);
        } else {
            binding2.imageArrow.setRotation(-90);
        }
        if (isLast) {
            binding2.seperationLine.setVisibility(View.GONE);
        }
        serviceSelectArea.addView(binding2.getRoot());
    }

    public void onClick(View view) {
        Utils.hideKeyboard(getActContext());
        int i = view.getId();
        if (i == R.id.backImgView) {
            getOnBackPressedDispatcher().onBackPressed();
        }
    }

    private class HelpMainAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> implements KmRecyclerView.KmStickyListener {

        @NonNull
        @Override
        public RecyclerView.ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            return new ViewHolder(ItemDesignCategoryHelpTitleBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

        @Override
        public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
            ViewHolder viewH = (ViewHolder) holder;

            JSONObject itemObj = generalFunc.getJsonObject(jsonArray, position);

            viewH.binding.shadowHeaderView.setVisibility(View.GONE);
            viewH.binding.titleTxtHelp.setText(generalFunc.getJsonValueStr("vTitle", itemObj));

            JSONArray mCategory = generalFunc.getJsonArray("Questions", itemObj);
            if (mCategory != null) {
                for (int j = 0; j < mCategory.length(); j++) {
                    HelpServices(generalFunc.getJsonObject(mCategory, j), viewH.binding.helpDetailsArea, j == (mCategory.length() - 1));
                }
            }
        }

        @Override
        public int getItemCount() {
            return jsonArray != null ? jsonArray.length() : 0;
        }

        //
        protected static class ViewHolder extends RecyclerView.ViewHolder {

            private final ItemDesignCategoryHelpTitleBinding binding;

            private ViewHolder(ItemDesignCategoryHelpTitleBinding binding) {
                super(binding.getRoot());
                this.binding = binding;
            }
        }

        //
        @Override
        public int getHeaderPositionForItem(int itemPosition) {
            return itemPosition;
        }

        @Override
        public int getHeaderLayout(int headerPosition) {
            return R.layout.item_design_category_help_title;
        }

        @Override
        public void bindHeaderData(View header, int headerPosition) {
            MTextView titleTxtHelp = header.findViewById(R.id.titleTxtHelp);
            LinearLayout subItemView = header.findViewById(R.id.subItemView);

            JSONObject itemObj = generalFunc.getJsonObject(jsonArray, headerPosition);
            titleTxtHelp.setText(generalFunc.getJsonValueStr("vTitle", itemObj));
            subItemView.setVisibility(View.GONE);

            View shadowHeaderView = header.findViewById(R.id.shadowHeaderView);
            shadowHeaderView.setVisibility(View.VISIBLE);
        }

        @Override
        public boolean isHeader(int itemPosition) {
            return false;
        }
    }
}