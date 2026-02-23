package com.adapter.files;

import android.content.Context;
import android.graphics.Color;
import android.graphics.PorterDuff;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;

import androidx.recyclerview.widget.RecyclerView;

import com.general.files.GeneralFunctions;
import com.buddyverse.main.R;
import com.buddyverse.main.databinding.FragmentBookingLayoutBinding;
import com.utils.LoadImage;
import com.utils.ServiceColor;
import com.utils.Utils;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 09-07-2016.
 */
public class AllHistoryRecycleAdapter extends RecyclerView.Adapter<RecyclerView.ViewHolder> {

    private static final int TYPE_ITEM = 1;
    private static final int TYPE_FOOTER = 2;
    public GeneralFunctions generalFunc;

    ArrayList<HashMap<String, String>> list;
    Context mContext;
    boolean isFooterEnabled = false;
    View footerView;

    FooterViewHolder footerHolder;
    private OnItemClickListener mItemClickListener;
    String userProfileJson;
    int size15_dp;
    int imagewidth;


    public AllHistoryRecycleAdapter(Context mContext, ArrayList<HashMap<String, String>> list, GeneralFunctions generalFunc, boolean isFooterEnabled) {
        this.mContext = mContext;
        this.list = list;
        this.generalFunc = generalFunc;
        this.isFooterEnabled = isFooterEnabled;
        userProfileJson = generalFunc.retrieveValue(Utils.USER_PROFILE_JSON);
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
        } else {
            return new ViewHolder(FragmentBookingLayoutBinding.inflate(LayoutInflater.from(parent.getContext()), parent, false));
        }

    }

    // Replace the contents of a view (invoked by the layout manager)
    @Override
    public void onBindViewHolder(final RecyclerView.ViewHolder holder, final int position) {


        if (holder instanceof ViewHolder) {
            final HashMap<String, String> item = list.get(position);
            final ViewHolder viewHolder = (ViewHolder) holder;
            boolean isAnyButtonShown = false;

            String vPackageName = item.get("vPackageName");
            if (Utils.checkText(vPackageName)) {
                viewHolder.binding.packageTxt.setVisibility(View.VISIBLE);
                viewHolder.binding.packageTxt.setText(vPackageName);
            } else {
                viewHolder.binding.packageTxt.setVisibility(View.GONE);
            }

            if (Utils.checkText(item.get("vName"))) {
                viewHolder.binding.driverNameTxt.setText(item.get("vName"));
                String image_url = item.get("vImage");
                if (Utils.checkText(image_url)) {
                    new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, imagewidth, imagewidth)), viewHolder.binding.driverImageView).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).setErrorImagePath(R.mipmap.ic_no_pic_user).build();
                } else {
                    viewHolder.binding.driverImageView.setImageResource(R.mipmap.ic_no_pic_user);
                }
            } else {
                viewHolder.binding.driverDataArea.setVisibility(View.GONE);
            }

            if (Utils.checkText(item.get("TripRating"))) {
                if (Double.parseDouble(item.get("TripRating")) > 0) {
                    viewHolder.binding.driverRatingVTxt.setText(item.get("TripRating"));
                } else {
                    viewHolder.binding.driverRatingView.setVisibility(View.GONE);
                }
            } else if (Utils.checkText(item.get("DriverAvgRating"))) {
                if (Double.parseDouble(item.get("DriverAvgRating")) > 0) {
                    viewHolder.binding.driverRatingVTxt.setText(item.get("DriverAvgRating"));
                    viewHolder.binding.driverRatingView.setVisibility(View.VISIBLE);
                } else {
                    viewHolder.binding.driverRatingView.setVisibility(View.GONE);
                }
            } else {
                viewHolder.binding.driverRatingView.setVisibility(View.GONE);
            }


            viewHolder.binding.totalHtxt.setText(generalFunc.retrieveLangLBl("", "LBL_Total_Fare_TXT"));
            viewHolder.binding.totalVtxt.setText(item.get("iFareNew"));
            viewHolder.binding.totalVtxt.setVisibility(View.VISIBLE);
            viewHolder.binding.totalHtxt.setVisibility(View.VISIBLE);
            viewHolder.binding.timeTxtHorizontal.setVisibility(View.GONE);
            viewHolder.binding.historyNoHTxt.setText(item.get("LBL_BOOKING_NO"));
            viewHolder.binding.historyNoVTxt.setText("#" + item.get("vRideNo"));
            /*String ConvertedTripRequestDate = item.get("ConvertedTripRequestDate");
            String ConvertedTripRequestTime = item.get("ConvertedTripRequestTime");
            if (ConvertedTripRequestDate != null) {
                viewHolder.binding.dateTxt.setText(ConvertedTripRequestDate);
                viewHolder.binding.timeTxt.setText(generalFunc.retrieveLangLBl("", "LBL_AT_TEXT") + " " + ConvertedTripRequestTime);
                viewHolder.binding.timeTxtHorizontal.setText(generalFunc.retrieveLangLBl("", "LBL_AT_TEXT") + " " + ConvertedTripRequestTime);
            }*/

            viewHolder.binding.dateTxt.setText(item.get("tDisplayDate"));
            viewHolder.binding.timeTxt.setText(item.get("tDisplayTime"));
            viewHolder.binding.timeTxtHorizontal.setText(item.get("tDisplayTime"));

            String tSaddress = item.get("tSaddress");
            viewHolder.binding.sourceAddressTxt.setText(tSaddress);
            viewHolder.binding.sAddressTxt.setText(tSaddress);
            if (tSaddress.equalsIgnoreCase("")) {
                viewHolder.binding.location.setVisibility(View.GONE);
            }
            viewHolder.binding.sourceAddressHTxt.setText(item.get("LBL_PICK_UP_LOCATION"));
            viewHolder.binding.destAddressHTxt.setText(item.get("LBL_DEST_LOCATION"));
            viewHolder.binding.interCityDestAddressHTxt.setText(item.get("LBL_INTERCITY_RETURN_LOCATION"));

            String vServiceTitle = item.get("vServiceTitle");

            if (vServiceTitle != null && !vServiceTitle.equalsIgnoreCase("")) {
                viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                viewHolder.binding.SelectedTypeNameTxt.setText(vServiceTitle);
            } else {
                //viewHolder.typeArea.setVisibility(View.GONE);
                String vVehicleType = item.get("vVehicleType");
                if (vVehicleType != null && !vVehicleType.equalsIgnoreCase("")) {
                    viewHolder.binding.SelectedTypeNameTxt.setText(vVehicleType);
                    viewHolder.binding.typeArea.setVisibility(View.VISIBLE);
                }
            }


            String tDaddress = item.get("tDaddress");
            if (Utils.checkText(tDaddress)) {
                viewHolder.binding.destarea.setVisibility(View.VISIBLE);
                viewHolder.binding.pickupLocArea.setPadding(0, 0, 0, size15_dp);
                viewHolder.binding.aboveLine.setVisibility(View.VISIBLE);
                viewHolder.binding.destAddressTxt.setText(tDaddress);
            } else {
                viewHolder.binding.destarea.setVisibility(View.GONE);
                viewHolder.binding.aboveLine.setVisibility(View.GONE);
                viewHolder.binding.pickupLocArea.setPadding(0, 0, 0, 0);

            }
            if (Utils.checkText(item.get("eIsInterCity")) && item.get("eIsInterCity").equalsIgnoreCase("Yes") && Utils.checkText(item.get("eRoundTrip")) && item.get("eRoundTrip").equalsIgnoreCase("Yes")) {
                if (Utils.checkText(item.get("tReturnAddress"))) {
                    viewHolder.binding.interCityDestAddressTxt.setText(item.get("tReturnAddress"));
                }
                if (Utils.checkText(item.get("tDropAddress"))) {
                    viewHolder.binding.destAddressTxt.setText(tDaddress);
                }

                viewHolder.binding.interCityDropOffLayout.setVisibility(View.VISIBLE);
                viewHolder.binding.squareImgView.setVisibility(View.VISIBLE);
                viewHolder.binding.dropoffLocArea.setPadding(0, 0, 0, size15_dp);
                viewHolder.binding.interCityaboveLine.setVisibility(View.VISIBLE);
                viewHolder.binding.imagedest.setVisibility(View.GONE);
            }

            String vBookingType = item.get("vBookingType");
            String eShowHistory = item.get("eShowHistory");
            String iActiveDisplay = item.get("iActiveDisplay");

            if (Utils.checkText(iActiveDisplay)) {
                viewHolder.binding.statusArea.setVisibility(View.VISIBLE);
                viewHolder.binding.statusVTxt.setText(iActiveDisplay);
            } else {
                viewHolder.binding.statusArea.setVisibility(View.GONE);
            }

            if (generalFunc.isRTLmode()) {
                viewHolder.binding.statusArea.setRotation(180);
                viewHolder.binding.statusVTxt.setRotation(180);
            }


            viewHolder.binding.SelectedTypeNameTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.SelectedTypeNameTxt.setSelected(true);
            viewHolder.binding.SelectedTypeNameTxt.setSingleLine(true);

            viewHolder.binding.statusVTxt.setEllipsize(TextUtils.TruncateAt.MARQUEE);
            viewHolder.binding.statusVTxt.setSelected(true);
            viewHolder.binding.statusVTxt.setSingleLine(true);


            viewHolder.binding.liveTrackBtn.setText(item.get("liveTrackLBL"));
            viewHolder.binding.viewDetailsBtn.setText(item.get("viewDetailLBL"));

            viewHolder.binding.SelectedTypeNameTxt.setTextColor(Color.parseColor(ServiceColor.UI_TEXT_COLORS[0]));


            viewHolder.binding.liveTrackBtnArea.setVisibility(View.GONE);
            viewHolder.binding.viewDetailsBtnArea.setVisibility(View.GONE);
            viewHolder.binding.reScheduleArea.setVisibility(View.GONE);
            viewHolder.binding.reScheduleBtnArea.setVisibility(View.GONE);
            viewHolder.binding.reBookingarea.setVisibility(View.GONE);
            viewHolder.binding.reBookingBtnArea.setVisibility(View.GONE);
            viewHolder.binding.cancelBookingBtnArea.setVisibility(View.GONE);
            viewHolder.binding.cancelArea.setVisibility(View.GONE);
            viewHolder.binding.viewCancelReasonBtnArea.setVisibility(View.GONE);
            viewHolder.binding.viewCancelReasonArea.setVisibility(View.GONE);
            viewHolder.binding.viewRequestedServiceBtnArea.setVisibility(View.GONE);
            viewHolder.binding.viewAdditionalChargesBtnArea.setVisibility(View.GONE);

            String eType = item.get("eType");
            int servicecolor = Color.parseColor(ServiceColor.UI_COLORS[position < ServiceColor.UI_COLORS.length ? position : position % ServiceColor.UI_COLORS.length]);
            if (eType != null) {
                switch (eType) {
                    case "Ride":
                        servicecolor = ServiceColor.RIDE.color;
                        break;
                    case "Deliver":
                    case "Multi-Delivery":
                        servicecolor = ServiceColor.PARCEL_DELIVERY.color;
                        break;
                    case "UberX":
                        if (item.get("isVideoCall").equalsIgnoreCase("Yes")) {
                            servicecolor = ServiceColor.VIDEO_CONSULTING.color;
                        } else {
                            servicecolor = ServiceColor.UFX.color;
                        }
                        break;
                }
            }
            viewHolder.binding.typeArea.getBackground().setColorFilter(servicecolor, PorterDuff.Mode.SRC_ATOP);
            boolean showUfxMultiArea = eType.equalsIgnoreCase(Utils.CabGeneralType_UberX) || eType.equalsIgnoreCase(Utils.eType_Multi_Delivery);

            if (showUfxMultiArea) {
                viewHolder.binding.driverDataArea.setVisibility(View.GONE);
                viewHolder.binding.ufxMultiArea.setVisibility(View.VISIBLE);
                viewHolder.binding.ufxMultiBtnArea.setVisibility(View.VISIBLE);

                viewHolder.binding.noneUfxMultiArea.setVisibility(View.GONE);
                viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.GONE);

                viewHolder.binding.providerNameTxt.setText(item.get("vName"));
                if (item.get("isVideoCall").equalsIgnoreCase("Yes")) {
                    viewHolder.binding.videoImageView.setVisibility(View.VISIBLE);
                } else {
                    viewHolder.binding.videoImageView.setVisibility(View.GONE);
                }

                String image_url = item.get("vImage");
                if (Utils.checkText(image_url)) {
                    new LoadImage.builder(LoadImage.bind(Utils.getResizeImgURL(mContext, image_url, imagewidth, imagewidth)), viewHolder.binding.providerImgView).setPlaceholderImagePath(R.mipmap.ic_no_pic_user).setErrorImagePath(R.mipmap.ic_no_pic_user).build();
                } else {
                    viewHolder.binding.providerImgView.setImageResource(R.mipmap.ic_no_pic_user);
                }

                viewHolder.binding.ratingBar.setRating(Float.parseFloat(item.get("vAvgRating")));
                if (Utils.checkText(item.get("TripRating"))) {
                    if (Double.parseDouble(item.get("TripRating")) > 0) {
                        viewHolder.binding.driverRating.setText(item.get("TripRating"));
                    } else {
                        viewHolder.binding.ratingview.setVisibility(View.GONE);
                    }
                } else {
                    viewHolder.binding.ratingview.setVisibility(View.GONE);
                }


            } else {
                viewHolder.binding.ufxMultiArea.setVisibility(View.GONE);
                viewHolder.binding.ufxMultiBtnArea.setVisibility(View.GONE);
                viewHolder.binding.noneUfxMultiArea.setVisibility(View.VISIBLE);
                viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.VISIBLE);
            }


            if (vBookingType.equalsIgnoreCase("schedule") /*&& showUfxMultiArea*/) {

                viewHolder.binding.viewRequestedServiceBtn.setText(item.get("LBL_VIEW_REQUESTED_SERVICES"));

                viewHolder.binding.viewAdditionalChargesBtn.setText(item.get("LBL_VERIFY_ADDITIONAL_CHARGES_TXT"));

                String LBL_RESCHEDULE = item.get("LBL_RESCHEDULE");
                viewHolder.binding.reScheduleBtn.setText(LBL_RESCHEDULE);
                viewHolder.binding.btnTypeReschedule.setText(LBL_RESCHEDULE);


                String LBL_REBOOKING = item.get("LBL_REBOOKING");
                viewHolder.binding.reBookingBtn.setText(LBL_REBOOKING);
                viewHolder.binding.btnTypeRebooking.setText(LBL_REBOOKING);

                String LBL_VIEW_REASON = item.get("LBL_VIEW_REASON");
                viewHolder.binding.viewCancelReasonBtn.setText(LBL_VIEW_REASON);
                viewHolder.binding.btnTypeViewCancelReason.setText(LBL_VIEW_REASON);

                String LBL_CANCEL_BOOKING = item.get("LBL_CANCEL_BOOKING");
                viewHolder.binding.cancelBookingBtn.setText(LBL_CANCEL_BOOKING);
                viewHolder.binding.btnTypeCancel.setText(LBL_CANCEL_BOOKING);


                if (item.get("showReScheduleBtn").equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.reScheduleArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.reScheduleBtnArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.totalVtxt.setVisibility(View.GONE);
                    viewHolder.binding.totalHtxt.setVisibility(View.GONE);
                    viewHolder.binding.timeTxt.setVisibility(View.GONE);
                    viewHolder.binding.timeTxtHorizontal.setVisibility(View.VISIBLE);
                }

                if (item.get("showReBookingBtn").equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.reBookingarea.setVisibility(View.VISIBLE);
                    viewHolder.binding.reBookingBtnArea.setVisibility(View.VISIBLE);
                }

                if (item.get("showCancelBookingBtn").equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.cancelBookingBtnArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.cancelArea.setVisibility(View.VISIBLE);
                    viewHolder.binding.totalVtxt.setVisibility(View.GONE);
                    viewHolder.binding.totalHtxt.setVisibility(View.GONE);
                    viewHolder.binding.timeTxt.setVisibility(View.GONE);
                    viewHolder.binding.timeTxtHorizontal.setVisibility(View.VISIBLE);
                }

                if (item.get("showViewCancelReasonBtn").equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.viewCancelReasonBtnArea.setVisibility(View.VISIBLE);
                }


                if (item.get("showViewRequestedServicesBtn").equalsIgnoreCase("Yes")) {
                    isAnyButtonShown = true;
                    viewHolder.binding.viewRequestedServiceBtnArea.setVisibility(View.VISIBLE);
                }


                viewHolder.binding.cancelBookingBtnArea.setOnClickListener(view -> btnClicked(view, position, "CancelBooking"));

                viewHolder.binding.cancelArea.setOnClickListener(view -> btnClicked(view, position, "CancelBooking"));

                viewHolder.binding.reBookingarea.setOnClickListener(view -> btnClicked(view, position, "ReBooking"));

                viewHolder.binding.reBookingBtnArea.setOnClickListener(view -> btnClicked(view, position, "ReBooking"));

                viewHolder.binding.viewRequestedServiceBtnArea.setOnClickListener(view -> {
                    if (mItemClickListener != null) {
                        mItemClickListener.onViewServiceClickList(view, position);
                    }

                });

                viewHolder.binding.reScheduleArea.setOnClickListener(view -> btnClicked(view, position, "ReSchedule"));

                viewHolder.binding.reScheduleBtnArea.setOnClickListener(view -> btnClicked(view, position, "ReSchedule"));


                viewHolder.binding.viewCancelReasonBtnArea.setOnClickListener(view -> btnClicked(view, position, "ViewCancelReason"));

                viewHolder.binding.viewCancelReasonArea.setOnClickListener(view -> btnClicked(view, position, "ViewCancelReason"));


            }

            double iFare = GeneralFunctions.parseDoubleValue(0, item.get("iFare"));
            if (iFare < 1) {
                viewHolder.binding.totalVtxt.setVisibility(View.GONE);
                viewHolder.binding.totalHtxt.setVisibility(View.GONE);
                viewHolder.binding.timeTxt.setVisibility(View.GONE);
                viewHolder.binding.timeTxtHorizontal.setVisibility(View.VISIBLE);
            }
            viewHolder.binding.viewAdditionalChargesBtn.setText(item.get("verifyChargesLBL"));

            if (item.get("showAdditionChargesBtn").equalsIgnoreCase("Yes")) {
                isAnyButtonShown = true;
                viewHolder.binding.viewAdditionalChargesBtnArea.setVisibility(View.VISIBLE);
            }


            viewHolder.binding.viewAdditionalChargesBtnArea.setOnClickListener(view -> btnClicked(view, position, "ViewAdditionalCharges"));

            if (item.get("showLiveTrackBtn").equalsIgnoreCase("Yes")) {
                isAnyButtonShown = true;
                viewHolder.binding.liveTrackBtnArea.setVisibility(View.VISIBLE);
            }

            viewHolder.binding.liveTrackBtnArea.setOnClickListener(view -> btnClicked(view, position, "LiveTrack"));

            if (item.get("showViewDetailBtn").equalsIgnoreCase("Yes")) {
                isAnyButtonShown = true;
                viewHolder.binding.viewDetailsBtnArea.setVisibility(View.VISIBLE);
            }

            if (!isAnyButtonShown) {
                if (showUfxMultiArea) {
                    viewHolder.binding.ufxMultiBtnArea.setVisibility(View.GONE);
                } else {
                    viewHolder.binding.noneUfxMultiBtnArea.setVisibility(View.GONE);

                }
            }

            viewHolder.binding.viewDetailsBtnArea.setOnClickListener(view -> btnClicked(view, position, "ViewDetail"));

            if (eShowHistory.equalsIgnoreCase("Yes")) {
                viewHolder.binding.contentArea.setOnClickListener(view -> {
                    btnClicked(view, position, "ShowDetail");
                });
            } else {
                viewHolder.binding.contentArea.setOnClickListener(null);
            }
        } else {
            FooterViewHolder footerHolder = (FooterViewHolder) holder;
            this.footerHolder = footerHolder;
        }


    }

    private void btnClicked(View view, int position, String type) {
        if (mItemClickListener != null) {
            mItemClickListener.onItemClickList(view, position, type);
        }
    }

    @Override
    public int getItemViewType(int position) {
        if (isPositionFooter(position) && isFooterEnabled == true) {
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
        if (isFooterEnabled == true) {
            return list.size() + 1;
        } else {
            return list.size();
        }

    }

    public void addFooterView() {
        this.isFooterEnabled = true;
        notifyDataSetChanged();
        if (footerHolder != null) footerHolder.progressArea.setVisibility(View.VISIBLE);
    }

    public void removeFooterView() {
        if (footerHolder != null) {
            isFooterEnabled = false;
            footerHolder.progressArea.setVisibility(View.GONE);
        }
    }


    public interface OnItemClickListener {
        void onItemClickList(View v, int position, String type);

        void onViewServiceClickList(View v, int position);

    }

    // inner class to hold a reference to each item of RecyclerView
    public class ViewHolder extends RecyclerView.ViewHolder {

        private FragmentBookingLayoutBinding binding;


        public ViewHolder(FragmentBookingLayoutBinding binding) {
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
}
