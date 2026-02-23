package com.act.homescreen23.adapter;

import android.annotation.SuppressLint;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CompoundButton;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.recyclerview.widget.RecyclerView;

import com.act.UberXHomeActivity;
import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.Item23UfxSubCategoryBinding;
import com.utils.LoadImage;
import com.utils.Utils;

import org.jetbrains.annotations.NotNull;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;

public class UFXSubCategory23ProAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private final UberXHomeActivity mActivity;
    private final GeneralFunctions generalFunc;

    public final ArrayList<String> multiServiceSelect = new ArrayList<>();
    public final ArrayList<String> multiServiceCategorySelect = new ArrayList<>();

    @Nullable
    private JSONArray mServiceArray;

    private CompoundButton lastCheckedRB = null;

    private boolean isMultiSelect = false, isRadioSelection = false;
    private final int grid;

    public UFXSubCategory23ProAdapter(@NonNull UberXHomeActivity activity, @Nullable JSONArray serviceArray) {
        this.mActivity = activity;
        this.mServiceArray = serviceArray;
        this.generalFunc = activity.generalFunc;

        updateData(isMultiSelect, isRadioSelection);
        this.grid = mActivity.getResources().getDimensionPixelSize(R.dimen.category_grid_size);
    }

    public void updateData(boolean isMultiSelect, boolean isRadioSelection) {
        this.isMultiSelect = isMultiSelect;
        this.isRadioSelection = isRadioSelection;
        if (lastCheckedRB != null) {
            if (lastCheckedRB.isChecked()) {
                lastCheckedRB.setChecked(false);
                lastCheckedRB = null;
            }
        }
    }

    @NotNull
    @Override
    public ViewHolder onCreateViewHolder(@NotNull ViewGroup parent, int viewType) {
        return new ViewHolder(Item23UfxSubCategoryBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @Override
    public void onBindViewHolder(@NonNull RecyclerView.ViewHolder parentViewHolder, final int position) {

        JSONObject mServiceObject = generalFunc.getJsonObject(mServiceArray, position);

        if (parentViewHolder instanceof ViewHolder viewHolder) {

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.arrowImageView.setRotation(180);
            }

            String vCategory = generalFunc.getJsonValueStr("vCategory", mServiceObject);
            if (vCategory != null) {
                if (vCategory.matches("\\w*")) {
                    viewHolder.binding.uberXCatNameTxtView.setMaxLines(1);

                    viewHolder.binding.uberXCatNameTxtView.setText(vCategory);
                } else {
                    viewHolder.binding.uberXCatNameTxtView.setMaxLines(2);

                    viewHolder.binding.uberXCatNameTxtView.setText(vCategory);
                }
            }

            String imageURL = Utils.getResizeImgURL(mActivity, generalFunc.getJsonValueStr("vLogo_image", mServiceObject), grid, grid);
            new LoadImage.builder(LoadImage.bind(imageURL), viewHolder.binding.catImgView).setErrorImagePath(R.mipmap.ic_no_icon).setPlaceholderImagePath(R.mipmap.ic_no_icon).build();

            viewHolder.binding.uberXCatNameTxtView.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
            viewHolder.binding.serviceCheckbox.setChecked(false);

            viewHolder.binding.contentArea.setOnClickListener(view -> {

                if (isMultiSelect) {
                    viewHolder.binding.serviceCheckbox.setChecked(!viewHolder.binding.serviceCheckbox.isChecked());
                } else {
                    if (isRadioSelection) {
                        viewHolder.binding.serviceradioBtn.performClick();
                    }
                }
            });

            viewHolder.binding.arrowImageView.setVisibility(View.GONE);
            if (isMultiSelect) {
                viewHolder.binding.serviceradioBtn.setVisibility(View.GONE);
                viewHolder.binding.serviceCheckbox.setVisibility(View.VISIBLE);
                viewHolder.binding.serviceCheckbox.setOnCheckedChangeListener((compoundButton, b) -> {
                            if (b) {
                                viewHolder.binding.uberXCatNameTxtView.setTextColor(mActivity.getResources().getColor(R.color.appThemeColor_1));
                            } else {
                                viewHolder.binding.uberXCatNameTxtView.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
                            }
                            String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", mServiceObject);
                            String iBiddingId = generalFunc.getJsonValueStr("iBiddingId", mServiceObject);
                            onMultiItem(Utils.checkText(iVehicleCategoryId) ? iVehicleCategoryId : iBiddingId, vCategory, b);
                        }
                );

                String isCheck = generalFunc.getJsonValueStr("isCheck", mServiceObject);
                if (isCheck != null && isCheck.equals("Yes")) {
                    viewHolder.binding.serviceCheckbox.setChecked(true);
                } else if (isCheck != null && isCheck.equals("No")) {
                    viewHolder.binding.serviceCheckbox.setChecked(false);
                }
            } else if (isRadioSelection) {
                viewHolder.binding.serviceradioBtn.setVisibility(View.VISIBLE);
                viewHolder.binding.serviceCheckbox.setVisibility(View.GONE);
                viewHolder.binding.serviceradioBtn.setOnCheckedChangeListener((compoundButton, b) -> {
                            if (lastCheckedRB != null) {
                                lastCheckedRB.setChecked(false);
                            }
                            lastCheckedRB = viewHolder.binding.serviceradioBtn;
                            if (b) {
                                viewHolder.binding.uberXCatNameTxtView.setTextColor(mActivity.getResources().getColor(R.color.appThemeColor_1));
                            } else {
                                viewHolder.binding.uberXCatNameTxtView.setTextColor(mActivity.getResources().getColor(R.color.text23Pro_Dark));
                            }
                            String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", mServiceObject);
                            String iBiddingId = generalFunc.getJsonValueStr("iBiddingId", mServiceObject);
                            onMultiItem(Utils.checkText(iVehicleCategoryId) ? iVehicleCategoryId : iBiddingId, vCategory, b);
                        }
                );
                String isCheck = generalFunc.getJsonValueStr("isCheck", mServiceObject);
                if (isCheck != null && isCheck.equals("Yes")) {
                    viewHolder.binding.serviceradioBtn.setChecked(true);
                } else if (isCheck != null && isCheck.equals("No")) {
                    viewHolder.binding.serviceradioBtn.setChecked(false);
                }
            } else {
                viewHolder.binding.serviceradioBtn.setVisibility(View.GONE);
                viewHolder.binding.serviceCheckbox.setVisibility(View.GONE);
                viewHolder.binding.arrowImageView.setVisibility(View.VISIBLE);
            }
        }
    }

    private void onMultiItem(String id, String category, boolean b) {
        if (b) {
            multiServiceSelect.add(id);
            multiServiceCategorySelect.add(category);
        } else {
            multiServiceSelect.remove(id);
            multiServiceCategorySelect.remove(category);
        }
    }

    @Override
    public int getItemCount() {
        return mServiceArray != null ? mServiceArray.length() : 0;
    }

    @SuppressLint("NotifyDataSetChanged")
    public void updateList(JSONArray serviceArray, boolean isMultiSelect, boolean isRadioSelection) {

        if (!multiServiceSelect.isEmpty()) {
            JSONArray tempServiceArray = new JSONArray();
            for (int pos = 0; pos < serviceArray.length(); pos++) {
                JSONObject mServiceObject = generalFunc.getJsonObject(serviceArray, pos);
                String iVehicleCategoryId = generalFunc.getJsonValueStr("iVehicleCategoryId", mServiceObject);
                String iBiddingId = generalFunc.getJsonValueStr("iBiddingId", mServiceObject);

                String id = Utils.checkText(iVehicleCategoryId) ? iVehicleCategoryId : iBiddingId;
                String vCategory = generalFunc.getJsonValueStr("vCategory", mServiceObject);

                try {
                    if (multiServiceSelect.contains(id) && multiServiceCategorySelect.contains(vCategory)) {
                        mServiceObject.put("isCheck", "Yes");
                    } else {
                        mServiceObject.put("isCheck", "No");
                    }
                } catch (JSONException e) {
                    throw new RuntimeException(e);
                }
                tempServiceArray.put(mServiceObject);
            }
            this.mServiceArray = tempServiceArray;
        } else {
            this.mServiceArray = serviceArray;
        }

        updateData(isMultiSelect, isRadioSelection);
        notifyDataSetChanged();
    }

    private static class ViewHolder extends RecyclerView.ViewHolder {
        private final Item23UfxSubCategoryBinding binding;

        private ViewHolder(Item23UfxSubCategoryBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}