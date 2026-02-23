package com.adapter.files;

import android.content.Context;
import android.graphics.Color;
import android.os.Handler;
import android.os.Looper;
import android.util.DisplayMetrics;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.RelativeLayout;

import androidx.annotation.NonNull;
import androidx.core.content.ContextCompat;
import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.utils.CommonUtilities;
import com.utils.LoadImage;
import com.utils.Utils;
import com.view.CreateRoundedView;
import com.view.MTextView;
import com.view.SelectableRoundedImageView;

import org.jetbrains.annotations.NotNull;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Objects;

/**
 * Created by Admin on 04-07-2016.
 */
public class CabTypeAdapter extends RecyclerView.Adapter<CabTypeAdapter.ViewHolder> {

    private final GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list_item;
    Context mContext;
    String vehicleIconPath = CommonUtilities.SERVER_URL + "webimages/icons/VehicleType/";
    String vehicleDefaultIconPath = CommonUtilities.SERVER_URL + "webimages/icons/DefaultImg/";
    String selectedVehicleTypeId = "", userProfileJson;

    OnItemClickList onItemClickList;
    ViewHolder viewHolder;
    boolean isVertical;
    public int cabCounter = 0, measuredHeight = 0;
    boolean isFirstTime = true;

    public CabTypeAdapter(Context mContext, ArrayList<HashMap<String, String>> list_item, GeneralFunctions generalFunc) {
        this.mContext = mContext;
        this.list_item = list_item;
        this.generalFunc = generalFunc;
        this.userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);

        isVertical = generalFunc.getJsonValue("VEHICLE_TYPE_SHOW_METHOD", userProfileJson) != null && generalFunc.getJsonValue("VEHICLE_TYPE_SHOW_METHOD", userProfileJson).equalsIgnoreCase("Vertical");
    }

    public void setRentalItem(ArrayList<HashMap<String, String>> list_item) {
        this.list_item = list_item;
    }

    @NonNull
    @Override
    public CabTypeAdapter.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        View view;
        if (isVertical) {
            view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_design_vertical_cab_type, parent, false);
        } else {
            view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_design_cab_type, parent, false);
        }
        viewHolder = new ViewHolder(view);
        return viewHolder;
    }

    @Override
    public void onBindViewHolder(@NotNull ViewHolder viewHolder, final int position) {
        setData(viewHolder, position);
    }

    public void setSelectedVehicleTypeId(String selectedVehicleTypeId) {
        this.selectedVehicleTypeId = selectedVehicleTypeId;
    }

    private void setData(CabTypeAdapter.ViewHolder viewHolder, final int position) {
        HashMap<String, String> item = list_item.get(position);
        String iVehicleTypeId = item.get("iVehicleTypeId");
        String SubTotal = item.get("SubTotal");

        String eRental = item.get("eRental");

        if (Utils.checkText(eRental) && eRental.equals("Yes")) {
            viewHolder.carTypeTitle.setText(item.get("vRentalVehicleTypeName"));
        } else {
            viewHolder.carTypeTitle.setText(item.get("vVehicleTypeName"));
        }

        String iPersonSize = item.get("iPersonSize");
        if (isVertical) {
            if (iPersonSize != null && !eRental.equals("")) {
                viewHolder.personsizeTxt.setVisibility(View.VISIBLE);
                viewHolder.personsizeTxt.setText(item.get("iPersonSize"));
            } else {
                viewHolder.personsizeTxt.setVisibility(View.GONE);
            }
        }


        boolean isHover = selectedVehicleTypeId.equals(iVehicleTypeId);

        if (Utils.checkText(SubTotal)) {
            viewHolder.totalfare.setText(generalFunc.convertNumberWithRTL(SubTotal));
        } else {
            viewHolder.infoimage.setVisibility(View.GONE);
            viewHolder.totalfare.setText("");
        }


        String imgUrl = "", imgName = "";
        if (isHover) {
            imgName = getImageName(Objects.requireNonNull(item.get("vLogo1")));
        } else {
            imgName = getImageName(Objects.requireNonNull(item.get("vLogo")));
        }

        if (imgName.equals("")) {
            /** Removing Default Icon, Where Cab Image Not Available*/
            viewHolder.carTypeImgView.setImageDrawable(ContextCompat.getDrawable(mContext, R.drawable.ic_vehicle_placeholder));
            viewHolder.carTypeImgViewselcted.setImageDrawable(ContextCompat.getDrawable(mContext, R.drawable.ic_vehicle_placeholder));
        } else {
            imgUrl = vehicleIconPath + iVehicleTypeId + "/android/" + imgName;
        }
        if (Utils.checkText(imgUrl)) {
            loadImage(viewHolder, imgUrl);
        }


        viewHolder.contentArea.setOnClickListener(view -> {
            if (onItemClickList != null) {
                onItemClickList.onItemClick(position);
            }
        });

        if (isHover) {
            viewHolder.imagareaselcted.setVisibility(View.VISIBLE);
            if (Utils.checkText(SubTotal)) {
                viewHolder.infoimage.setVisibility(View.VISIBLE);
            }
            viewHolder.imagarea.setVisibility(View.GONE);

            int color = mContext.getResources().getColor(R.color.appThemeColor_2);
            viewHolder.carTypeTitle.setTextColor(color);
            if (isVertical) {
                viewHolder.carTypeTitle.setTextColor(mContext.getResources().getColor(R.color.black));
                if (Utils.checkText(SubTotal)) {
                    viewHolder.infoimage.setVisibility(View.VISIBLE);
                }
            }
            if (!isVertical) {
                new CreateRoundedView(mContext.getResources().getColor(R.color.white), (int) mContext.getResources().getDimension(R.dimen._30sdp), 2,
                        color, viewHolder.carTypeImgViewselcted);
                viewHolder.carTypeImgViewselcted.setBorderColor(color);
            }

            if (isVertical) {
                viewHolder.contentArea.setBackgroundColor(Color.parseColor("#ededed"));
                viewHolder.carTypeDesc.setText(item.get("tInfoText"));
                viewHolder.carTypeDesc.setVisibility(View.VISIBLE);
            }

        } else {
            if (isVertical) {
                viewHolder.contentArea.setBackgroundColor(mContext.getResources().getColor(R.color.white));
                viewHolder.llArea.setBackgroundColor(mContext.getResources().getColor(R.color.white));
                viewHolder.carTypeDesc.setText(item.get("tInfoText"));
                viewHolder.carTypeDesc.setVisibility(View.VISIBLE);
                if (Utils.checkText(SubTotal)) {
                    viewHolder.infoimage.setVisibility(View.VISIBLE);
                }
            }
            viewHolder.imagareaselcted.setVisibility(View.GONE);
            viewHolder.imagarea.setVisibility(View.VISIBLE);
            viewHolder.carTypeTitle.setTextColor(mContext.getResources().getColor(R.color.black));


            int color = Color.parseColor("#cbcbcb");

            if (!isVertical) {
                new CreateRoundedView(Color.parseColor("#ffffff"), (int) mContext.getResources().getDimension(R.dimen._30sdp), 2, color, viewHolder.carTypeImgView);
                viewHolder.carTypeImgView.setBorderColor(color);
            } else {
                new Handler(Looper.getMainLooper()).postDelayed(() -> {
                    if (isFirstTime) {
                        cabCounter++;
                        if (cabCounter <= 3) {
                            measuredHeight += viewHolder.llArea.getMeasuredHeight();
                        }
                        if ((list_item.size() > 3 && cabCounter == 3) || (list_item.size() <= 3 && cabCounter == list_item.size())) {
                            isFirstTime = false;
                            onItemClickList.onHeightMeasured(measuredHeight, cabCounter);
                        }
                    }
                }, 50);
            }

        }


    }

    private String getImageName(String vLogo) {
        String imageName;

        if (vLogo.equals("")) {
            return vLogo;
        }

        DisplayMetrics metrics = (mContext.getResources().getDisplayMetrics());
        int densityDpi = (int) (metrics.density * 160f);

        switch (densityDpi) {
            case DisplayMetrics.DENSITY_LOW:
            case DisplayMetrics.DENSITY_MEDIUM:
                imageName = "mdpi_" + vLogo;
                break;
            case DisplayMetrics.DENSITY_HIGH:

            case DisplayMetrics.DENSITY_TV:
                imageName = "hdpi_" + vLogo;
                break;
            case DisplayMetrics.DENSITY_XHIGH:

            case DisplayMetrics.DENSITY_280:
                imageName = "xhdpi_" + vLogo;
                break;
            case DisplayMetrics.DENSITY_XXXHIGH:

            case DisplayMetrics.DENSITY_560:
                imageName = "xxxhdpi_" + vLogo;
                break;
            case DisplayMetrics.DENSITY_XXHIGH:
            case DisplayMetrics.DENSITY_420:

            case DisplayMetrics.DENSITY_360:

            case DisplayMetrics.DENSITY_400:
            default:
                imageName = "xxhdpi_" + vLogo;
                break;
        }

        return imageName;
    }

    private void loadImage(final CabTypeAdapter.ViewHolder holder, String imageUrl) {

        new LoadImage.builder(LoadImage.bind(imageUrl), holder.carTypeImgView).setErrorImagePath(R.drawable.ic_vehicle_placeholder).setPlaceholderImagePath(R.drawable.ic_vehicle_placeholder).build();

        new LoadImage.builder(LoadImage.bind(imageUrl), holder.carTypeImgViewselcted).setErrorImagePath(R.drawable.ic_vehicle_placeholder).setPlaceholderImagePath(R.drawable.ic_vehicle_placeholder).build();

    }

    @Override
    public int getItemCount() {
        if (list_item == null) {
            return 0;
        }
        return list_item.size();
    }

    public void setOnItemClickList(OnItemClickList onItemClickList) {
        this.onItemClickList = onItemClickList;
    }

    public interface OnItemClickList {
        void onItemClick(int position);

        void onHeightMeasured(int measuredHeight, int cabCounter);
    }

    public static class ViewHolder extends RecyclerView.ViewHolder {

        SelectableRoundedImageView carTypeImgView, carTypeImgViewselcted;
        MTextView carTypeTitle, carTypeDesc, personsizeTxt;
        View llArea;
        RelativeLayout contentArea;
        MTextView totalfare;
        FrameLayout imagarea, imagareaselcted;
        ImageView infoimage;

        private ViewHolder(View view) {
            super(view);

            carTypeImgView = view.findViewById(R.id.carTypeImgView);
            personsizeTxt = view.findViewById(R.id.personsizeTxt);
            carTypeImgViewselcted = view.findViewById(R.id.carTypeImgViewselcted);
            carTypeTitle = view.findViewById(R.id.carTypeTitle);
            carTypeDesc = view.findViewById(R.id.carTypeDesc);
            llArea = view.findViewById(R.id.llArea);
            contentArea = view.findViewById(R.id.contentArea);
            totalfare = view.findViewById(R.id.totalfare);
            imagarea = view.findViewById(R.id.imagarea);
            imagareaselcted = view.findViewById(R.id.imagareaselcted);
            infoimage = view.findViewById(R.id.infoimage);
        }
    }
}

