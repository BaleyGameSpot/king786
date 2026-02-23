package com.act.homescreen23.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.os.Handler;
import android.os.Looper;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.GridLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23MoreServicesBinding;
import com.buddyverse.main.databinding.ItemMedicalServiceSectionBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONObject;

public class MoreService23Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_HEADER = 0, TYPE_LIST = 1;
    private final Context mContext;
    private final GeneralFunctions generalFunc;
    @Nullable
    private JSONArray mServicesArr;
    private final OnClickListener listener;

    private final int dynamicHeight, dynamicWidth;
    private boolean isNewHome_23 = false;

    public MoreService23Adapter(@NonNull Context context, @NonNull GeneralFunctions generalFunctions, @NonNull JSONArray servicesArr, @NonNull OnClickListener listener) {
        this.mContext = context;
        this.generalFunc = generalFunctions;
        this.mServicesArr = servicesArr;
        this.listener = listener;

        this.dynamicHeight = mContext.getResources().getDimensionPixelSize(R.dimen._60sdp);
        this.dynamicWidth = mContext.getResources().getDimensionPixelSize(R.dimen._60sdp);

        if (mContext instanceof UberXHomeActivity activity) {
            isNewHome_23 = activity.homeDynamic_23_fragment != null;
        }
    }

    @NotNull
    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        if (viewType == TYPE_HEADER) {
            return new MedicalViewHolder(ItemMedicalServiceSectionBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        } else {
            return new ViewHolder(Item23MoreServicesBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }
    }

    @SuppressLint({"RecyclerView", "SetTextI18n"})
    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder holder, int position) {
        JSONObject mServiceObject = generalFunc.getJsonObject(mServicesArr, position);

        if (holder instanceof MedicalViewHolder medicalHolder) {
            medicalHolder.binding.eTypeTxt.setText(generalFunc.getJsonValueStr("vTitle", mServiceObject));

            if (mServiceObject.has("Services")) {
                if (generalFunc.getJsonValueStr("GridView", mServiceObject).equalsIgnoreCase("Yes")) {
                    medicalHolder.binding.dataListRecyclerViewMore.setLayoutManager(new GridLayoutManager(mContext, 2));
                } else {
                    medicalHolder.binding.dataListRecyclerViewMore.setLayoutManager(new GridLayoutManager(mContext, getNumOfColumns() <= 4 ? 3 : getNumOfColumns()));
                }

                JSONArray serviceArr = generalFunc.getJsonArray("Services", mServiceObject);
                MoreService23Adapter moreS23Adapter = new MoreService23Adapter(mContext, generalFunc, serviceArr, (morePos, insideServiceObject) -> {
                    //
                    listener.onMoreServiceItemClick(position, insideServiceObject);
                });
                medicalHolder.binding.dataListRecyclerViewMore.setAdapter(moreS23Adapter);
            }

        } else if (holder instanceof ViewHolder bHolder) {

            bHolder.binding.catNameTxt.setText(generalFunc.getJsonValueStr("vCategory", mServiceObject));

            bHolder.binding.catDescTxt.setVisibility(View.GONE);
            if (mServiceObject.has("tListDescription")) {
                String tDesc = generalFunc.getJsonValueStr("tListDescription", mServiceObject);
                if (Utils.checkText(tDesc)) {
                    bHolder.binding.catDescTxt.setVisibility(View.VISIBLE);
                    bHolder.binding.catDescTxt.setText(tDesc);

                    new Handler(Looper.getMainLooper()).postDelayed(() -> {
                        if (bHolder.binding.catNameTxt.getLineCount() == 1) {
                            bHolder.binding.catNameTxt.setMinLines(1);
                            bHolder.binding.catDescTxt.setMinLines(4);
                        }
                    }, 20);
                }

            }

            String vListLogo = generalFunc.getJsonValueStr("vListLogo", mServiceObject);
            String imageURL = Utils.getResizeImgURL(mContext, vListLogo != null ? vListLogo : "", dynamicWidth, dynamicHeight);

            if (isNewHome_23) {
                boolean isRoundImage = generalFunc.getJsonValueStr("isRoundImage", mServiceObject).equalsIgnoreCase("Yes");
                bHolder.binding.catImgView.setBorderWidthDP(1);
                bHolder.binding.catImgView.setOval(isRoundImage);
                new LoadImage.builder(LoadImage.bind(imageURL), bHolder.binding.catImgView)
                        .setErrorImagePath(isRoundImage ? R.drawable.ic_circle_image_bg : R.color.imageBg)
                        .setPlaceholderImagePath(isRoundImage ? R.drawable.ic_circle_image_bg : R.color.imageBg).build();
            } else {
                bHolder.binding.catImgView.setBorderWidthDP(0);
                new LoadImage.builder(LoadImage.bind(imageURL), bHolder.binding.catImgView).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
            }

            //////////////////-----------------------------
            bHolder.binding.contentArea.setOnClickListener(v -> listener.onMoreServiceItemClick(position, mServiceObject));
        }
    }

    @Override
    public int getItemViewType(int position) {
        try {
            if (mServicesArr != null && mServicesArr.length() > 0) {
                JSONObject moreObject = generalFunc.getJsonObject(mServicesArr, 0);
                if (moreObject.has("GridView")) {
                    return TYPE_HEADER;
                }
            }
            return TYPE_LIST;
        } catch (Exception e) {
            return TYPE_LIST;
        }
    }

    @Override
    public int getItemCount() {
        return mServicesArr != null ? mServicesArr.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateData(JSONArray servicesArr) {
        this.mServicesArr = servicesArr;
        notifyDataSetChanged();
    }

    private Integer getNumOfColumns() {
        try {
            float screenWidth_int_value = Utils.getScreenPixelWidth(mContext) - mContext.getResources().getDimensionPixelSize(R.dimen._15sdp);
            return (int) (screenWidth_int_value / mContext.getResources().getDimensionPixelSize(R.dimen._70sdp));
        } catch (Exception e) {
            e.printStackTrace();
        }
        return -1;
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final Item23MoreServicesBinding binding;

        private ViewHolder(Item23MoreServicesBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    private static class MedicalViewHolder extends RecyclerView.ViewHolder {
        private final ItemMedicalServiceSectionBinding binding;

        private MedicalViewHolder(ItemMedicalServiceSectionBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onMoreServiceItemClick(int morePos, JSONObject mServiceObject);
    }
}