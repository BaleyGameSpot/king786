package com.act.rentItem.adapter;

import android.annotation.SuppressLint;
import android.content.Context;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.ItemRentItemPostBinding;
import com.utils.LoadImageGlide;
import com.utils.Utils;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

public class RentItemDataRecommendedAdapter extends RecyclerView.Adapter<RentItemDataRecommendedAdapter.DataViewHolder> {

    private final GeneralFunctions generalFunc;
    private final OnClickListener onClickListener;
    private final ArrayList<HashMap<String, String>> list;
    private final int imgHeight;
    String LBL_RENT_PER;

    public RentItemDataRecommendedAdapter(@NonNull Context context, @NonNull GeneralFunctions generalFunc, int div, @NonNull ArrayList<HashMap<String, String>> list, @NonNull OnClickListener listener) {
        this.list = list;
        this.generalFunc = generalFunc;
        this.onClickListener = listener;

        LBL_RENT_PER = generalFunc.retrieveLangLBl("", "LBL_RENT_PER");

        int imgWidth = (int) (Utils.getScreenPixelWidth(context) - context.getResources().getDimensionPixelSize(R.dimen._20sdp)) / div;
        if (div == 1) {
            imgWidth = (int) (Utils.getScreenPixelWidth(context) - context.getResources().getDimensionPixelSize(R.dimen._10sdp)) / div;
        }
        this.imgHeight = (int) (imgWidth / 1.77);
    }

    @NonNull
    @Override
    public DataViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int i) {
        return new DataViewHolder(ItemRentItemPostBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
    }

    @SuppressLint("SetTextI18n")
    @Override
    public void onBindViewHolder(@NonNull DataViewHolder holder, int position) {
        HashMap<String, String> mapData = list.get(position);

        holder.binding.txtPostPrice.setText(mapData.get("fAmount"));
        holder.binding.txtPostTitle.setText(mapData.get("vItemName"));
        holder.binding.postAddressTxt.setText(mapData.get("vLocation"));
        holder.binding.postAddressArea.setVisibility(Objects.requireNonNull(mapData.get("eIsUserAddressDisplay")).equalsIgnoreCase("Yes") ? View.VISIBLE : View.INVISIBLE);

        if (Utils.checkText(mapData.get("eRentItemDuration"))) {
            holder.binding.txtPostDurationStatus.setVisibility(View.VISIBLE);
            holder.binding.txtPostDurationStatus.setText(LBL_RENT_PER + " " + mapData.get("eRentItemDuration"));
        } else {
            holder.binding.txtPostDurationStatus.setVisibility(View.GONE);
        }

        if (Utils.checkText(mapData.get("eFeatured"))) {
            holder.binding.rentPostStatusTagTxt.setText(mapData.get("eFeatured"));
            holder.binding.rentPostStatusTagTxt.setVisibility(View.VISIBLE);
            if (generalFunc.isRTLmode()) {
                holder.binding.rentPostStatusTagTxt.setBackground(ContextCompat.getDrawable(holder.itemView.getContext(), R.drawable.right_radius_rtl_1));
            }
        } else {
            holder.binding.rentPostStatusTagTxt.setVisibility(View.GONE);
        }

        /*if (Utils.checkText(mapData.get("eListingType"))) {
            holder.binding.typeNameTxt.setText(mapData.get("eListingType"));
            holder.binding.cardViewTypeArea.setVisibility(View.VISIBLE);
            if (mapData.containsKey("typeBGColor") && Utils.checkText(mapData.get("typeBGColor"))) {
                holder.binding.cardViewTypeArea.setCardBackgroundColor(Color.parseColor(mapData.get("typeBGColor")));
            }
        } else {
            holder.binding.cardViewTypeArea.setVisibility(View.INVISIBLE);
        }*/
        holder.binding.cardViewTypeArea.setVisibility(View.GONE);

        if (Objects.requireNonNull(mapData.get("eIsFavourite")).equalsIgnoreCase("Yes")) {
            holder.binding.ivFav.setImageResource(R.drawable.ic_heart_on_fav_store_new);
        } else {
            holder.binding.ivFav.setImageResource(R.drawable.ic_heart_off_fav_store_new);
        }
        // TODO : favorite force fully | GONE
        holder.binding.ivFav.setVisibility(View.GONE);

        // TODO : User Approve Area force fully | GONE
        //holder.binding.userApproveArea.setVisibility(Objects.requireNonNull(mapData.get("eStatusOrg")).equalsIgnoreCase("Approved") ? View.VISIBLE : View.GONE);
        holder.binding.userApproveArea.setVisibility(View.GONE);

        // Images
        LinearLayout.LayoutParams params = (LinearLayout.LayoutParams) holder.binding.photosArea.getLayoutParams();
        //params.width = imgWidth;
        params.height = (imgHeight + holder.itemView.getContext().getResources().getDimensionPixelSize(R.dimen._14sdp));
        holder.binding.photosArea.setLayoutParams(params);

        RelativeLayout.LayoutParams params1 = (RelativeLayout.LayoutParams) holder.binding.ivRentItemImage.getLayoutParams();
        //params1.width = imgWidth;
        params1.height = imgHeight;
        holder.binding.ivRentItemImage.setLayoutParams(params1);

        JSONArray imagesArr = generalFunc.getJsonArray(mapData.get("Images"));
        String imageUrl = "";
        if (imagesArr != null && imagesArr.length() > 0) {
            JSONObject obj_temp = generalFunc.getJsonObject(imagesArr, 0);

            String eFileType = generalFunc.getJsonValueStr("eFileType", obj_temp);
            String vImage = generalFunc.getJsonValueStr("vImage", obj_temp);
            String ThumbImage = generalFunc.getJsonValueStr("ThumbImage", obj_temp);

            if (eFileType.equals("Image")) {
                if (!TextUtils.isEmpty(vImage)) {
                    imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), vImage, 0, params1.height);
                }
            } else if (eFileType.equals("Video")) {

                if (!TextUtils.isEmpty(ThumbImage)) {
                    imageUrl = Utils.getResizeImgURL(holder.itemView.getContext(), ThumbImage, 0, params1.height);
                }
            }
        }
        new LoadImageGlide.builder(holder.itemView.getContext(), LoadImageGlide.bind(imageUrl), holder.binding.ivRentItemImage).setErrorImagePath(R.color.imageBg).setPlaceholderImagePath(R.color.imageBg).build();
        holder.binding.itemArea.setOnClickListener(v -> onClickListener.onItemClick(position, mapData));
    }

    @Override
    public int getItemCount() {
        return list.size();
    }

    protected static class DataViewHolder extends RecyclerView.ViewHolder {

        private final ItemRentItemPostBinding binding;

        private DataViewHolder(ItemRentItemPostBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    public interface OnClickListener {
        void onItemClick(int position, HashMap<String, String> mapData);
    }
}