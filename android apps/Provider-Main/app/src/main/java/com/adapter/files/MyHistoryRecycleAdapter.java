package com.adapter.files;

import android.content.Context;
import android.graphics.Color;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.providers.R;
import com.buddyverse.providers.databinding.ItemMyHistoryDesignBinding;
import com.model.ServiceModule;
import com.utils.LoadImage;
import com.utils.ServiceColor;
import com.utils.Utils;
import com.view.MTextView;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import java.util.Objects;

/**
 * Created by Admin on 09-07-2016.
 */
public class MyHistoryRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    private static final int TYPE_HEADER = 3;
    public GeneralFunctions generalFunc;
    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;
    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;

    JSONObject userProfileJsonObj;
    int size15_dp;
    int imagewidth;

    public MyHistoryRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        size15_dp = (int) mContext.getResources().getDimension(R.dimen._15sdp);
        imagewidth = (int) mContext.getResources().getDimension(R.dimen._50sdp);
    }

    public void setOnItemClickListener(OnItemClickListener mItemClickListener) {
        this.mItemClickListener = mItemClickListener;
    }

    @Override
    public RecyclerView.ViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        if (viewType == TYPE_FOOTER) {
            View v = LayoutInflater.from(parent.getContext()).inflate(R.layout.footer_list, parent, false);
            this.footerView = v;
            return new FooterViewHolder(v);
        } else if (viewType == TYPE_HEADER) {
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.earning_amount_layout, parent, false);
            return new HeaderViewHolder(view);
        } else {
            return new ViewHolder(ItemMyHistoryDesignBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof final ViewHolder viewHolder) {
            final HashMap<String, String> item = list.get(position);
            boolean isAnyButtonShown = false;

            String vPackageName = item.get("vPackageName");
            if (vPackageName != null && !vPackageName.equalsIgnoreCase("")) {
                viewHolder.binding.packageTxt.setVisibility(View.VISIBLE);
                viewHolder.binding.packageTxt.setText(vPackageName);
            } else {
                viewHolder.binding.packageTxt.setVisibility(View.GONE);
            }

            String vModel = item.get("vModel");
            if (Utils.checkText(vModel)) {
                viewHolder.binding.txtCarModel.setVisibility(View.VISIBLE);
                viewHolder.binding.txtCarModel.setText(vModel);
            } else {
                viewHolder.binding.txtCarModel.setVisibility(View.GONE);
            }

            String vLicencePlateNo = item.get("vLicencePlateNo");
            if (Utils.checkText(vLicencePlateNo)) {
                viewHolder.binding.txtCarLicenceNo.setVisibility(View.VISIBLE);
                viewHolder.binding.txtCarLicenceNo.setText(vLicencePlateNo);
            } else {
                viewHolder.binding.txtCarLicenceNo.setVisibility(View.GONE);
            }


            viewHolder.binding.myBookingNoHTxt.setText(item.get("LBL_BOOKING_NO"));
            if (Utils.checkText(item.get("vRideNo"))) {
                viewHolder.binding.myBookingNoVTxt.setText("#" + item.get("vRideNo"));
            }

            /*String ConvertedTripRequestDate = item.get("ConvertedTripRequestDate");
            String ConvertedTripRequestTime = item.get("ConvertedTripRequestTime");
            if (ConvertedTripRequestDate != null) {
                viewHolder.binding.dateTxt.setText(ConvertedTripRequestDate);
                viewHolder.binding.timeTxt.setText(ConvertedTripRequestTime);
            }*/
            viewHolder.binding.dateTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.timeTxt.setText(item.get("tDisplayTime"));


            viewHolder.binding.sourceAddressTxt.setText(item.get("tSaddress"));
            viewHolder.binding.sAddressTxt.setText(item.get("tSaddress"));
            viewHolder.binding.destAddressHTxt.setText(item.get("LBL_DEST_LOCATION"));
            viewHolder.binding.sourceAddressHTxt.setText(item.get("LBL_PICK_UP_LOCATION"));
            viewHolder.binding.interCityDestAddressHTxt.setText(item.get("LBL_INTERCITY_RETURN_LOCATION"));


            String vServiceTitle = item.get("vServiceTitle");
            if (vServiceTitle != null && !vServiceTitle.equalsIgnoreCase("")) {
                viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                viewHolder.binding.typeArea1.setVisibility(View.GONE);
                viewHolder.binding.SelectedTypeNameTxt.setText(vServiceTitle);
                viewHolder.binding.SelectedTypeNameTxt1.setText(vServiceTitle);
            } else if (item.get("vVehicleType") != null && !Objects.requireNonNull(item.get("vVehicleType")).equalsIgnoreCase("")) {
                viewHolder.binding.typeArea.setVisibility(View.GONE);
                viewHolder.binding.typeArea1.setVisibility(View.VISIBLE);
                viewHolder.binding.SelectedTypeNameTxt.setText(item.get("vVehicleType"));
                viewHolder.binding.SelectedTypeNameTxt1.setText(item.get("vVehicleType"));
            } else {
                viewHolder.binding.typeArea.setVisibility(View.GONE);
                viewHolder.binding.typeArea1.setVisibility(View.GONE);
            }

            String tDaddress = item.get("tDaddress");
            if (Utils.checkText(tDaddress) || ServiceModule.IsTrackingProvider) {
                viewHolder.binding.destarea.setVisibility(View.VISIBLE);
                viewHolder.binding.pickupLocArea.setPadding(0, 0, 0, size15_dp);
                viewHolder.binding.aboveLine.setVisibility(View.VISIBLE);
                tDaddress = Objects.requireNonNull(tDaddress).equalsIgnoreCase("") ? "----" : tDaddress;
                viewHolder.binding.destAddressTxt.setText(tDaddress);
            } else {
                viewHolder.binding.destarea.setVisibility(View.GONE);
                viewHolder.binding.aboveLine.setVisibility(View.GONE);
                viewHolder.binding.pickupLocArea.setPadding(0, 0, 0, 0);

            }

            String eIsInterCity = item.get("eIsInterCity");
            String eRoundTrip = item.get("eRoundTrip");

            if (Utils.checkText(eIsInterCity) && eIsInterCity.equalsIgnoreCase("Yes") && Utils.checkText(eRoundTrip) && eRoundTrip.equalsIgnoreCase("Yes")) {
                viewHolder.binding.destAddressTxt.setText(item.get("tDropAddress"));
                viewHolder.binding.interCityDestAddressTxt.setText(item.get("tReturnAddress"));
                viewHolder.binding.imagedest.setVisibility(View.GONE);
                viewHolder.binding.squareImgView.setVisibility(View.VISIBLE);
                viewHolder.binding.belowLine.setVisibility(View.VISIBLE);
                viewHolder.binding.interCityDestArea.setVisibility(View.VISIBLE);
                viewHolder.binding.dropToLocation.setPadding(0, 0, 0, size15_dp);
            } else {
                viewHolder.binding.imagedest.setVisibility(View.VISIBLE);
                viewHolder.binding.squareImgView.setVisibility(View.GONE);
                viewHolder.binding.belowLine.setVisibility(View.GONE);
                viewHolder.binding.interCityDestArea.setVisibility(View.GONE);
                viewHolder.binding.dropToLocation.setPadding(0, 0, 0, 0);
            }

            String vBookingType = item.get("vBookingType");
            String iActiveDisplay = item.get("iActiveDisplay");

            if (Utils.checkText(iActiveDisplay)) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.statusVTxt.setText(iActiveDisplay);
            }

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.statusArea.setRotation(180);
                viewHolder.binding.statusVTxt.setRotation(180);
            }

            viewHolder.binding.SelectedTypeNameTxt1.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.SelectedTypeNameTxt1.setSelected(true);
            viewHolder.binding.SelectedTypeNameTxt1.setSingleLine(true);

            viewHolder.binding.SelectedTypeNameTxt.setSelected(true);
            viewHolder.binding.SelectedTypeNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.SelectedTypeNameTxt.setSingleLine(true);

            viewHolder.binding.statusVTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.statusVTxt.setSelected(true);
            viewHolder.binding.statusVTxt.setSingleLine(true);

            String eType = item.get("eType");
            int servicecolor = Color.parseColor(ServiceColor.UI_COLORS[position < ServiceColor.UI_COLORS.length ? position : position % ServiceColor.UI_COLORS.length]);
            if (eType != null) {
                switch (eType) {
                    case "Ride" -> servicecolor = ServiceColor.RIDE.color;
                    case "Deliver", "Multi-Delivery" ->
                            servicecolor = ServiceColor.PARCEL_DELIVERY.color;
                    case "UberX" -> servicecolor = ServiceColor.UFX.color;
                }
            }

            viewHolder.binding.typeArea1.setCardBackgroundColor(servicecolor);
            viewHolder.binding.typeArea.setCardBackgroundColor(servicecolor);
            if (Utils.checkText(item.get("vService_TEXT_color"))) {
                viewHolder.binding.SelectedTypeNameTxt1.setTextColor(Color.parseColor(item.get("vService_TEXT_color")));
            }

            boolean showUfxMultiArea = Objects.requireNonNull(eType).equalsIgnoreCase(Utils.CabGeneralType_UberX) || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery);
            if (vServiceTitle != null && !vServiceTitle.equalsIgnoreCase("")) {
                viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
            }
            viewHolder.binding.sAddressTxt.setVisibility(View.VISIBLE);
            viewHolder.binding.ratingBar.setVisibility(View.VISIBLE);
            viewHolder.binding.fareArea.setVisibility(View.GONE);


            viewHolder.binding.cancelBookingBtnArea.setVisibility(View.GONE);
            viewHolder.binding.cancelArea.setVisibility(View.GONE);
            viewHolder.binding.viewCancelReasonBtnArea.setVisibility(View.GONE);
            viewHolder.binding.viewCancelReasonArea.setVisibility(View.GONE);
            viewHolder.binding.viewRequestedServiceBtnArea.setVisibility(View.GONE);
            viewHolder.binding.startTripArea.setVisibility(View.GONE);
            viewHolder.binding.startTripBtnArea.setVisibility(View.GONE);


            if (showUfxMultiArea || Objects.requireNonNull(vBookingType).equalsIgnoreCase("history")) {
                viewHolder.binding.ufxMultiArea.setVisibility(View.VISIBLE);
                viewHolder.binding.ufxMultiBtnArea.setVisibility(View.VISIBLE);

                viewHolder.binding.noneUfxMultiArea.setVisibility(View.GONE);
                viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.GONE);

                viewHolder.binding.userNameTxt.setText(item.get("vName"));

                String image_url = item.get("vImage");

                if (Utils.checkText(image_url)) {
                    new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, imagewidth, imagewidth)), viewHolder.binding.userImgView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                } else {
                    viewHolder.binding.userImgView.setImageResource(R.mipmap.ic_no_pic_user);

                }


                viewHolder.binding.ratingBar.setRating(Float.parseFloat(Objects.requireNonNull(item.get("vAvgRating"))));

                if (Objects.requireNonNull(vBookingType).equalsIgnoreCase("history")) {

                    if (Utils.checkText(image_url)) {
                        new LoadImage.builder(LoadImage.bind(image_url), viewHolder.binding.userImageView).setErrorImagePath(R.mipmap.ic_no_pic_user).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).build();
                    } else {
                        viewHolder.binding.userImageView.setImageResource(R.mipmap.ic_no_pic_user);
                    }

                    viewHolder.binding.fareTxt.setText(item.get("iFare"));


                    if (vServiceTitle != null && !vServiceTitle.equalsIgnoreCase("")) {
                        viewHolder.binding.typeArea1.setVisibility(View.VISIBLE);
                        viewHolder.binding.typeArea.setVisibility(View.GONE);
                    }

                    viewHolder.binding.sAddressTxt.setVisibility(View.GONE);
                    viewHolder.binding.userImgView.setVisibility(View.GONE);
                    viewHolder.binding.userImageArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.ratingBar.setVisibility(View.GONE);
                    viewHolder.binding.fareArea.setVisibility(View.VISIBLE);
                } else {
                    if (vServiceTitle != null && !vServiceTitle.equalsIgnoreCase("")) {
                        viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                        viewHolder.binding.typeArea1.setVisibility(View.GONE);
                    }

                    viewHolder.binding.sAddressTxt.setVisibility(View.VISIBLE);
                    viewHolder.binding.userImgView.setVisibility(View.VISIBLE);
                    viewHolder.binding.userImageArea.setVisibility(View.GONE);
                    viewHolder.binding.ratingBar.setVisibility(View.VISIBLE);
                    viewHolder.binding.fareArea.setVisibility(View.GONE);
                }

            } else {
                viewHolder.binding.ufxMultiArea.setVisibility(View.GONE);
                viewHolder.binding.ufxMultiBtnArea.setVisibility(View.GONE);

                viewHolder.binding.noneUfxMultiArea.setVisibility(View.VISIBLE);
                viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.VISIBLE);
            }

            String LBL_ACCEPT_JOB = item.get("LBL_ACCEPT_JOB");
            String LBL_START_TRIP = item.get("LBL_START_TRIP");
            String LBL_DECLINE_JOB = item.get("LBL_DECLINE_JOB");
            String LBL_CANCEL_TRIP = item.get("LBL_CANCEL_TRIP");
            String LBL_START_JOB = item.get("LBL_START_JOB");
            String LBL_CANCEL_JOB = item.get("LBL_CANCEL_JOB");
            String LBL_ACCEPT = item.get("LBL_ACCEPT");
            String LBL_DECLINE = item.get("LBL_DECLINE");

            if ((vBookingType.equalsIgnoreCase("schedule") || vBookingType.equalsIgnoreCase("pending"))/* && showUfxMultiArea*/) {
                viewHolder.binding.viewRequestedServiceBtn.setText(item.get("LBL_VIEW_REQUESTED_SERVICES"));

                String showViewRequestedServicesBtn = item.get("showViewRequestedServicesBtn");
                if (Utils.checkText(showViewRequestedServicesBtn) && showViewRequestedServicesBtn.equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.viewRequestedServiceBtnArea.setVisibility(View.VISIBLE);
                }


                String LBL_VIEW_REASON = item.get("LBL_VIEW_REASON");
                viewHolder.binding.viewCancelReasonBtn.setText(LBL_VIEW_REASON);
                viewHolder.binding.btnTypeViewCancelReason.setText(LBL_VIEW_REASON);

                String showCancelBtn = item.get("showCancelBtn");
                if (Utils.checkText(showCancelBtn) && showCancelBtn.equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.cancelBookingBtn.setText(LBL_CANCEL_TRIP);
                    viewHolder.binding.btnTypeCancel.setText(LBL_CANCEL_TRIP);

                    viewHolder.binding.cancelBookingBtnArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.cancelArea.setVisibility(View.VISIBLE);

                    if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                        viewHolder.binding.cancelBookingBtn.setText(LBL_CANCEL_JOB);
                        viewHolder.binding.btnTypeCancel.setText(LBL_CANCEL_JOB);
                    }
                }

                String showViewCancelReasonBtn = item.get("showViewCancelReasonBtn");
                if (Utils.checkText(showViewCancelReasonBtn) && showViewCancelReasonBtn.equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.viewCancelReasonBtnArea.setVisibility(View.VISIBLE);
                }

                String showStartBtn = item.get("showStartBtn");
                if (Utils.checkText(showStartBtn) && showStartBtn.equalsIgnoreCase("Yes")) {
                    viewHolder.binding.btnTypeStart.setText(LBL_START_TRIP);
                    viewHolder.binding.startTripBtn.setText(LBL_START_TRIP);
                    isAnyButtonShown = true;
                    viewHolder.binding.startTripArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.startTripBtnArea.setVisibility(View.VISIBLE);
                    if (eType.equalsIgnoreCase(Utils.CabGeneralType_UberX)) {
                        viewHolder.binding.btnTypeStart.setText(LBL_START_JOB);
                        viewHolder.binding.startTripBtn.setText(LBL_START_JOB);
                    }
                }


                String showAcceptBtn = item.get("showAcceptBtn");
                if (Utils.checkText(showAcceptBtn) && showAcceptBtn.equalsIgnoreCase("Yes")) {
                    viewHolder.binding.btnTypeStart.setText(Objects.requireNonNull(item.get("vBookingType")).equalsIgnoreCase("schedule") ? LBL_ACCEPT : LBL_ACCEPT_JOB);
                    viewHolder.binding.startTripBtn.setText(Objects.requireNonNull(item.get("vBookingType")).equalsIgnoreCase("schedule") ? LBL_ACCEPT : LBL_ACCEPT_JOB);
                    isAnyButtonShown = true;
                    viewHolder.binding.startTripArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.startTripBtnArea.setVisibility(View.VISIBLE);
                }

                String showDeclineBtn = item.get("showDeclineBtn");
                if (Utils.checkText(showDeclineBtn) && showDeclineBtn.equalsIgnoreCase("Yes")) {
                    viewHolder.binding.cancelBookingBtn.setText(Objects.requireNonNull(item.get("vBookingType")).equalsIgnoreCase("schedule") ? LBL_DECLINE : LBL_DECLINE_JOB);
                    viewHolder.binding.btnTypeCancel.setText(Objects.requireNonNull(item.get("vBookingType")).equalsIgnoreCase("schedule") ? LBL_DECLINE : LBL_DECLINE_JOB);
                    isAnyButtonShown = true;
                    viewHolder.binding.cancelBookingBtnArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.cancelArea.setVisibility(View.VISIBLE);
                }


                viewHolder.binding.cancelBookingBtnArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onCancelBookingClickList(view, position);
                    }
                });

                viewHolder.binding.cancelArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onCancelBookingClickList(view, position);
                    }
                });


                viewHolder.binding.startTripArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onTripStartClickList(view, position);
                    }
                });

                viewHolder.binding.startTripBtnArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onTripStartClickList(view, position);
                    }
                });

                viewHolder.binding.viewRequestedServiceBtnArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onViewServiceClickList(view, position);
                    }
                });
            }

            String eShowHistory = item.get("eShowHistory");

            if (Utils.checkText(eShowHistory) && eShowHistory.equalsIgnoreCase("Yes")) {
                viewHolder.binding.contentView.setOnClickListener(v -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onDetailViewClickList(v, position);
                    }
                });

            } else {
                viewHolder.binding.contentView.setOnClickListener(null);
            }


            if (!isAnyButtonShown) {
                if (showUfxMultiArea) {
                    viewHolder.binding.ufxMultiBtnArea.setVisibility(View.GONE);
                } else {
                    viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.GONE);

                }
            }
            if (item.containsKey("eShownMessage") && Utils.checkText(item.get("eShownMessage"))) {
                viewHolder.binding.pendingReqMsgTxt.setText(item.get("eShownMessage"));
                viewHolder.binding.requestAcceptedNote.setVisibility(View.VISIBLE);
            }

            if (Utils.checkText(item.get("eHailTrip")) && Objects.requireNonNull(item.get("eHailTrip")).equalsIgnoreCase("Yes")) {
                viewHolder.binding.userImageArea.setVisibility(View.GONE);
            }

        } else if (holder instanceof HeaderViewHolder headerHolder) {
            Map<String, String> map = list.get(position);

            headerHolder.tripsCountTxt.setText(map.get("TripCount"));
            headerHolder.fareTxt.setText(map.get("TotalEarning"));
            headerHolder.avgRatingCalcTxt.setText(map.get("AvgRating"));

            if (map.containsKey("isPast") && Objects.requireNonNull(map.get("isPast")).equalsIgnoreCase("yes")) {
                headerHolder.earnedAmountArea.setVisibility(View.VISIBLE);
            } else {
                headerHolder.earnedAmountArea.setVisibility(View.GONE);
            }

        } else {
            this.footerHolder = (FooterViewHolder) holder;
        }


    }

    @Override
    public int getItemViewType(int position) {

        if ((position) == 0) {
            return TYPE_HEADER;
        }
        if (isPositionFooter(position) && isFooterEnabled) {
            return TYPE_FOOTER;
        }
        return TYPE_ITEM;
    }

    private boolean isPositionFooter(int position) {
        return position == list.size();
    }

    // Return the size of your itemsData (invoked by the layout manager)
    @Override
    public int getItemCount() {
        if (isFooterEnabled && list.size() > 0) {
            return list.size() + 1;
        } else {
            return list.size();
        }

    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null)
            footerHolder.progressArea.setVisibility(View.GONE);
    }


    public interface OnItemClickListener {

        void onCancelBookingClickList(View v, int position);

        void onTripStartClickList(View v, int position);

        void onViewServiceClickList(View v, int position);

        void onDetailViewClickList(View v, int position);
    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {
        private final ItemMyHistoryDesignBinding binding;

        public ViewHolder(ItemMyHistoryDesignBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }

    class FooterViewHolder extends RecyclerView.ViewHolder {
        LinearLayout progressArea;

        public FooterViewHolder(View itemView) {
            super(itemView);

            progressArea = (LinearLayout) itemView;

        }
    }


    class HeaderViewHolder extends RecyclerView.ViewHolder {
        LinearLayout earnedAmountArea;
        MTextView earnTitleTxt, fareTxt, tripsCompletedTxt, tripsCountTxt, avgRatingTxt, avgRatingCalcTxt;

        public HeaderViewHolder(View view) {
            super(view);
            earnTitleTxt = view.findViewById(R.id.earnTitleTxt);
            fareTxt = view.findViewById(R.id.fareTxt);
            tripsCompletedTxt = view.findViewById(R.id.tripsCompletedTxt);
            tripsCountTxt = view.findViewById(R.id.tripsCountTxt);
            avgRatingTxt = view.findViewById(R.id.avgRatingTxt);
            avgRatingCalcTxt = view.findViewById(R.id.avgRatingCalcTxt);
            earnedAmountArea = view.findViewById(R.id.earnedAmountArea);


            earnTitleTxt.setText(generalFunc.retrieveLangLBl("", "LBL_TOTAL_EARNINGS"));
            tripsCompletedTxt.setText(generalFunc.retrieveLangLBl("Completed Trips", "LBL_TOTAL_SERVICES"));
            avgRatingTxt.setText(generalFunc.retrieveLangLBl("Avg. Rating", "LBL_AVG_RATING"));
        }
    }
}
