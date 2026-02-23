package com.adapter.files;

import android.annotation.SuppressLint;
import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CompoundButton;
import android.widget.ImageView;
import android.widget.RadioButton;

import androidx.annotation.NonNull;
import androidx.appcompat.widget.AppCompatCheckBox;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.utils.LoadImage;
import com.utils.Logger;
import com.utils.Utils;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class UberXSubCategory22Adapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final Context mContext;
    private final GeneralFunctions generalFunc;
    private ArrayList<HashMap<String, String>> list_item;
    OnItemClickList onItemClickList;

    private final String userProfileJson;
    private CompoundButton lastCheckedRB = null;

    private boolean isMultiSelect = false, isRadioSelection = false;
    int grid;

    public UberXSubCategory22Adapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
        updateData();
        grid = mContext.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
    }

    private void updateData() {
        if (generalFunc.getJsonValue("SERVICE_PROVIDER_FLOW", userProfileJson).equalsIgnoreCase("PROVIDER")) {
            isMultiSelect = true;
        }
        isRadioSelection = false;
        if (list_item != null && list_item.size() > 0) {
            if (list_item.get(0).get("iBiddingId") != null && !Objects.requireNonNull(list_item.get(0).get("iBiddingId")).equalsIgnoreCase("") ||
                    list_item.get(0).get("VideoConsultSection") != null && Objects.requireNonNull(list_item.get(0).get("VideoConsultSection")).equalsIgnoreCase("Yes")) {
                isMultiSelect = false;
                isRadioSelection = true;
            }
        }
        if (lastCheckedRB != null) {
            if (lastCheckedRB.isChecked()) {
                lastCheckedRB.setChecked(false);
                lastCheckedRB = null;
            }
        }
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new ViewHolder(LayoutInflater.from(parent.getContext()).inflate(R.layout.item_uberx_cat_list_design, parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder parentViewHolder, final int position) {

        HashMap<String, String> item = list_item.get(position);

        String vCategory = item.get("vCategory");
        String vLogo_image = item.get("vLogo_image");

        if (parentViewHolder instanceof ViewHolder) {
            ViewHolder viewHolder = (ViewHolder) parentViewHolder;

            try {
                if (generalFunc.isRTLmode()) {
                    if (viewHolder.arrowImageView != null) {
                        viewHolder.arrowImageView.setRotation(180);
                    }
                }
            } catch (Exception e) {
                Logger.e("Exception","::"+e.getMessage());
            }
            if (vCategory != null) {
                if (vCategory.matches("\\w*")) {
                    viewHolder.uberXCatNameTxtView.setMaxLines(1);

                    viewHolder.uberXCatNameTxtView.setText(vCategory);
                } else {
                    viewHolder.uberXCatNameTxtView.setMaxLines(2);

                    viewHolder.uberXCatNameTxtView.setText(vCategory);
                }
            }

            String imageURL = null;
            if (vLogo_image != null) {
                imageURL = Utils.getResizeImgURL(mContext, vLogo_image, grid, grid);


                new LoadImage.builder(LoadImage.bind(imageURL), viewHolder.catImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();
            }

            new LoadImage.builder(LoadImage.bind(imageURL), viewHolder.catImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

            viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.black));
            viewHolder.serviceCheckbox.setChecked(false);

            viewHolder.contentArea.setOnClickListener(view -> {

                if (isMultiSelect) {
                    viewHolder.serviceCheckbox.setChecked(!viewHolder.serviceCheckbox.isChecked());
                } else {
                    if (isRadioSelection) {
                        if (viewHolder.serviceradioBtn != null) {
                            viewHolder.serviceradioBtn.performClick();
                        }
                    } else {
                        if (onItemClickList != null) {
                            onItemClickList.onItemClick(position);
                        }
                    }
                }
            });

            if (viewHolder.arrowImageView != null) {
                viewHolder.arrowImageView.setVisibility(View.GONE);
            }
            if (isMultiSelect) {
                viewHolder.serviceradioBtn.setVisibility(View.GONE);
                viewHolder.serviceCheckbox.setVisibility(View.VISIBLE);
                viewHolder.serviceCheckbox.setOnCheckedChangeListener((compoundButton, b) -> {
                            if (b) {
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.appThemeColor_1));
                            } else {
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.black));
                            }
                            onItemClickList.onMultiItem(Objects.requireNonNull(item.get("iVehicleCategoryId")).equalsIgnoreCase("") ? item.get("iBiddingId") : item.get("iVehicleCategoryId"), item.get("vCategory"), b);
                        }
                );

                String isCheck = item.get("isCheck");
                if (isCheck != null && isCheck.equals("Yes")) {
                    viewHolder.serviceCheckbox.setChecked(true);
                } else if (isCheck != null && isCheck.equals("No")) {
                    viewHolder.serviceCheckbox.setChecked(false);
                }
            } else if (isRadioSelection) {
                viewHolder.serviceradioBtn.setVisibility(View.VISIBLE);
                viewHolder.serviceCheckbox.setVisibility(View.GONE);
                viewHolder.serviceradioBtn.setOnCheckedChangeListener((compoundButton, b) -> {
                            if (lastCheckedRB != null) {
                                lastCheckedRB.setChecked(false);
                            }
                            lastCheckedRB = viewHolder.serviceradioBtn;
                            if (b) {
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.appThemeColor_1));
                            } else {
                                viewHolder.uberXCatNameTxtView.setTextColor(mContext.getResources().getColor(R.color.black));
                            }
                            onItemClickList.onMultiItem(Objects.requireNonNull(item.get("iVehicleCategoryId")).equalsIgnoreCase("") ? item.get("iBiddingId") : item.get("iVehicleCategoryId"), item.get("vCategory"), b);
                        }
                );
                String isCheck = item.get("isCheck");
                if (isCheck != null && isCheck.equals("Yes")) {
                    viewHolder.serviceradioBtn.setChecked(true);
                } else if (isCheck != null && isCheck.equals("No")) {
                    viewHolder.serviceradioBtn.setChecked(false);
                }
            } else {
                viewHolder.serviceradioBtn.setVisibility(View.GONE);
                viewHolder.serviceCheckbox.setVisibility(View.GONE);
                if (viewHolder.arrowImageView != null) {
                    viewHolder.arrowImageView.setVisibility(View.VISIBLE);
                }
            }
        }
    }

    @Override
    public int getItemCount() {
        return list_item.size();
    }

    @Override
    public int getItemViewType(int position) {
        return 0;
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateList(ArrayList<HashMap<String, String>> list_item) {
        this.list_item = list_item;
        updateData();
        notifyDataSetChanged();
    }

    public interface OnItemClickList {
        void onItemClick(int position);

        void onMultiItem(String id, String category, boolean b);
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {

        private final MTextView uberXCatNameTxtView;
        private final View contentArea;
        private final SelectableRoundedImageView catImgView;
        private final ImageView arrowImageView;
        private final AppCompatCheckBox serviceCheckbox;
        private final RadioButton serviceradioBtn;

        public ViewHolder(View view) {
            super(view);

            uberXCatNameTxtView = view.findViewById(R.id.uberXCatNameTxtView);
            contentArea = view.findViewById(R.id.contentArea);
            catImgView = view.findViewById(R.id.catImgView);
            arrowImageView = view.findViewById(R.id.arrowImageView);
            serviceCheckbox = view.findViewById(R.id.serviceCheckbox);
            serviceradioBtn = view.findViewById(R.id.serviceradioBtn);

        }
    }
}