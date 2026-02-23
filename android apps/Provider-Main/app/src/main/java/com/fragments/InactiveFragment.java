package com.fragments;

import android.content.Context;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.act.AddVehicleActivity24;
import com.act.ListOfDocumentActivity;
import com.act.MainActivity;
import com.act.MainActivity_22;
import com.act.ManageVehiclesActivity;
import com.act.SetAvailabilityActivity;
import com.act.UfxCategoryActivity;
import com.act.UploadDocTypeWiseActivity;
import com.adapter.files.InactiveRecycleAdapter;
import com.general.SkeletonViewHandler;
import com.general.files.ActUtils;
import com.general.files.CustomDialog;
import com.general.files.GeneralFunctions;
import com.general.files.MyApp;
import com.buddyverse.providers.R;
import com.model.ServiceModule;
import com.service.handler.ApiHandler;
import com.utils.Utils;
import com.view.MButton;
import com.view.MaterialRippleLayout;

import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;

/**
 * Created by Admin on 19-06-2017.
 */

public class InactiveFragment extends Fragment implements InactiveRecycleAdapter.OnItemClickList {

    View view;
    private RecyclerView mRecyclerView;
    ArrayList<HashMap<String, String>> list;
    InactiveRecycleAdapter inactiveRecycleAdapter;
    GeneralFunctions generalFunc;

    View contentArea;

    boolean isdocprogress = false;
    boolean isvehicleprogress = false;
    boolean isServiceProcessComplete = false;
    boolean isdriveractive = false;
    boolean isavailable = false;
    JSONObject userProfileJsonObj;
    MButton btn_type2;
    int submitBtnId;
    boolean isbtnClick = false;
    public int totalVehicles = 0;
    public boolean isUfxServicesEnabled = true;

    String LBL_UPLOAD_DOCS_NOTE, LBL_MANAGE_VEHICLES, LBL_ADD_VEHICLE, LBL_ADD_VEHICLE_AND_DOC_NOTE, LBL_SERVICE_ADD_SUCCESS;
    private Context mContext;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {

        view = inflater.inflate(R.layout.fragment_inactive, container, false);

        if (MyApp.getInstance().getCurrentAct() instanceof MainActivity) {
            MainActivity mainActivity = (MainActivity) requireActivity();
            generalFunc = mainActivity.generalFunc;
            mContext = mainActivity.getActContext();
        } else if (MyApp.getInstance().getCurrentAct() instanceof MainActivity_22) {
            MainActivity_22 mainActivity_22 = (MainActivity_22) requireActivity();
            generalFunc = mainActivity_22.generalFunc;
            mContext = mainActivity_22.getActContext();
        }

        mRecyclerView = (RecyclerView) view.findViewById(R.id.inActiveRecyclerView);
        contentArea = view.findViewById(R.id.contentArea);

        btn_type2 = ((MaterialRippleLayout) view.findViewById(R.id.btn_type2)).getChildView();
        mRecyclerView.setLayoutManager(new LinearLayoutManager(getActivity(), LinearLayoutManager.VERTICAL, false));
        mRecyclerView.setHasFixedSize(true);

        userProfileJsonObj = generalFunc.getJsonObject(generalFunc.retrieveValue(Utils.USER_PROFILE_JSON));
        String UFX_SERVICE_AVAILABLE = generalFunc.getJsonValueStr("UFX_SERVICE_AVAILABLE", userProfileJsonObj);
        isUfxServicesEnabled = !Utils.checkText(UFX_SERVICE_AVAILABLE) || UFX_SERVICE_AVAILABLE.equalsIgnoreCase("Yes");

        submitBtnId = Utils.generateViewId();
        btn_type2.setId(submitBtnId);

        btn_type2.setText(generalFunc.retrieveLangLBl("", "LBL_CHECK_ACC_STATUS"));
        btn_type2.setOnClickListener(new setOnClickList());

        LBL_UPLOAD_DOCS_NOTE = generalFunc.retrieveLangLBl("We need to verify your driving documents to activate your account.", "LBL_UPLOAD_DOCS_NOTE");
        LBL_MANAGE_VEHICLES = generalFunc.retrieveLangLBl("Manage Vehicles", "LBL_MANAGE_VEHICLES");
        LBL_ADD_VEHICLE = generalFunc.retrieveLangLBl("Add Vehicle", "LBL_ADD_VEHICLE");
        LBL_SERVICE_ADD_SUCCESS = generalFunc.retrieveLangLBl("", "LBL_SERVICE_ADD_SUCCESS");
        LBL_ADD_VEHICLE_AND_DOC_NOTE = generalFunc.retrieveLangLBl("Please add your vehicles and its document. After that we will verify its registration.", "LBL_ADD_VEHICLE_AND_DOC_NOTE");

        SkeletonViewHandler.getInstance().ShowNormalSkeletonView(contentArea, R.layout.skeleton_inactive_frag);
        return view;
    }

    private void setData() {
        list = new ArrayList<>();
        HashMap<String, String> map1 = new HashMap<>();
        map1.put("title", generalFunc.retrieveLangLBl("Registration Successful", "LBL_REGISTRATION_SUCCESS"));
        map1.put("msg", "");
        map1.put("btn", "");
        map1.put("line", "start");
        map1.put("state", "true");
        list.add(map1);

        HashMap<String, String> map3 = new HashMap<>();
        if (!isvehicleprogress && !isServiceProcessComplete) {

            if (ServiceModule.DeliverAllProduct) {
                map3.put("title", generalFunc.retrieveLangLBl("Add vehicles with document", "LBL_ADD_VEHICLE_AND_DOC"));
                map3.put("msg", LBL_ADD_VEHICLE_AND_DOC_NOTE);

                if (totalVehicles > 0) {
                    map3.put("btn", LBL_MANAGE_VEHICLES);
                } else {
                    map3.put("btn", LBL_ADD_VEHICLE);
                }
            } else {

                if (ServiceModule.ServiceProviderProduct) {
                    map3.put("title", generalFunc.retrieveLangLBl("Add your services", "LBL_ADD_SERVICE_TITLE"));
                    map3.put("msg", generalFunc.retrieveLangLBl("Please select your services as per your expertise and industry.", "LBL_ADD_SERVICE_NOTE"));
                    map3.put("btn", generalFunc.retrieveLangLBl("Select Services", "LBL_SELECT_SERVICE"));
                } else {

                    if (ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) {
                        map3.put("title", generalFunc.retrieveLangLBl("", "LBL_ADD_SERVICES_AND_DOC"));
                        map3.put("msg", generalFunc.retrieveLangLBl("Please add your vehicles and its document. After that we will verify its registration.", "LBL_ADD_SERVICES_AND_DOC_NOTE"));
                    } else {
                        map3.put("title", generalFunc.retrieveLangLBl("Add vehicles with document", "LBL_ADD_VEHICLE_AND_DOC"));
                        map3.put("msg", LBL_ADD_VEHICLE_AND_DOC_NOTE);
                    }
                    if (ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) {
                        map3.put("btn", generalFunc.retrieveLangLBl("Manage Vehicles", "LBL_MANANGE_SERVICES"));
                    } else {
                        if (totalVehicles > 0) {
                            map3.put("btn", LBL_MANAGE_VEHICLES);
                        } else {
                            map3.put("btn", LBL_ADD_VEHICLE);
                        }
                    }
                }
            }

            map3.put("line", "two");
            map3.put("state", isvehicleprogress + "");
            map3.put("serviceState", isServiceProcessComplete + "");
        } else {
            if (ServiceModule.isServiceProviderAvailable()) {
                map3.put("title", generalFunc.retrieveLangLBl("Your Service added successfully.", "LBL_SERVICE_ADD_SUCCESS"));
            } else {
                map3.put("title", generalFunc.retrieveLangLBl("Your vehicle added successfully.", "LBL_VEHICLE_ADD_SUCCESS"));
            }
            map3.put("msg", "");
            map3.put("btn", ServiceModule.isServiceProviderAvailable() ? generalFunc.retrieveLangLBl("Manage Vehicles", "LBL_MANANGE_SERVICES") : "");
            map3.put("line", "two");
            map3.put("state", isvehicleprogress + "");
            map3.put("serviceState", isServiceProcessComplete + "");
        }
        list.add(map3);

        HashMap<String, String> map2 = new HashMap<>();
        if (!isdocprogress) {
            map2.put("title", generalFunc.retrieveLangLBl("Upload your documents", "LBL_UPLOAD_YOUR_DOCS"));

            map2.put("msg", LBL_UPLOAD_DOCS_NOTE);

            map2.put("btn", generalFunc.retrieveLangLBl("Upload Document", "LBL_UPLOAD_DOC"));
        } else {
            map2.put("title", generalFunc.retrieveLangLBl("Upload your Documents Successful", "LBL_UPLOADDOC_SUCCESS"));
            map2.put("msg", "");
            map2.put("btn", "");

        }
        map2.put("line", "three");
        map2.put("state", isdocprogress + "");

        list.add(map2);

        if (ServiceModule.ServiceProviderProduct || (ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) && !ServiceModule.DeliverAllProduct) {

            if (!isavailable) {
                HashMap<String, String> map4 = new HashMap<>();
                map4.put("title", generalFunc.retrieveLangLBl(" Add your availability", "LBL_ADD_YOUR_AVAILABILITY"));
                map4.put("msg", generalFunc.retrieveLangLBl("Add your availability for scheduled booking requests ", "LBL_ADD_AVAILABILITY_DOC_NOTE"));
                map4.put("btn", generalFunc.retrieveLangLBl("", "LBL_SET_AVAILABILITY_TXT"));
                map4.put("line", "four");
                map4.put("state", isavailable + "");
                list.add(map4);
            } else {
                HashMap<String, String> map4 = new HashMap<>();
                map4.put("title", generalFunc.retrieveLangLBl("Availability set successfully", "LBL_AVAILABILITY_ADD_SUCESS_MSG"));
                map4.put("msg", "");
                map4.put("btn", "");
                map4.put("line", "four");
                map4.put("state", isavailable + "");
                list.add(map4);
            }

            if (isdriveractive) {
                HashMap<String, String> map5 = new HashMap<>();
                map5.put("title", generalFunc.retrieveLangLBl("", "LBL_ADMIN_APPROVE"));
                map5.put("msg", "");
                map5.put("btn", "");
                map5.put("line", "end");
                map5.put("state", isdriveractive + "");
                list.add(map5);
            } else {
                HashMap<String, String> map5 = new HashMap<>();
                map5.put("title", generalFunc.retrieveLangLBl("Waiting for admin's approval", "LBL_WAIT_ADMIN_APPROVE"));
                map5.put("msg", generalFunc.retrieveLangLBl("We will check your provided information and get back to you soon.", "LBL_WAIT_ADMIN_APPROVE_NOTE"));
                map5.put("btn", "");
                map5.put("line", "end");
                map5.put("state", isdriveractive + "");
                list.add(map5);
            }

        } else {

            if (isdriveractive) {
                HashMap<String, String> map4 = new HashMap<>();
                map4.put("title", generalFunc.retrieveLangLBl("", "LBL_ADMIN_APPROVE"));
                map4.put("msg", "");
                map4.put("btn", "");
                map4.put("line", "end");
                map4.put("state", isdriveractive + "");
                list.add(map4);
            } else {
                HashMap<String, String> map4 = new HashMap<>();
                map4.put("title", generalFunc.retrieveLangLBl("Waiting for admin's approval", "LBL_WAIT_ADMIN_APPROVE"));
                map4.put("msg", generalFunc.retrieveLangLBl("We will check your provided information and get back to you soon.", "LBL_WAIT_ADMIN_APPROVE_NOTE_DRIVER"));
                map4.put("btn", "");
                map4.put("line", "end");
                map4.put("state", isdriveractive + "");
                list.add(map4);

            }
        }

        inactiveRecycleAdapter = new InactiveRecycleAdapter(mContext, list, generalFunc);
        mRecyclerView.setAdapter(inactiveRecycleAdapter);
        inactiveRecycleAdapter.setOnItemClickList(this);
    }

    @Override
    public void onItemClick(int position) {
        Utils.hideKeyboard(getActivity());
        if (position == 2) {
            Bundle bn = new Bundle();
            bn.putString("PAGE_TYPE", "Driver");
            bn.putString("doc_file", "");
            bn.putString("iDriverVehicleId", "");
            new ActUtils(mContext).startActWithData(ListOfDocumentActivity.class, bn);


        } else if (position == 1) {

            Bundle bn = new Bundle();

            if (ServiceModule.DeliverAllProduct) {
                if (totalVehicles > 0) {
                    new ActUtils(getActivity()).startActWithData(ManageVehiclesActivity.class, bn);
                } else {
                    new ActUtils(getActivity()).startActWithData(AddVehicleActivity24.class, bn);
                }
                return;
            }

            String eShowVehicles = generalFunc.getJsonValueStr("eShowVehicles", userProfileJsonObj);
            if (ServiceModule.ServiceBid) {

                bn.putString("selView", "vehicle");
                bn.putInt("totalVehicles", 1);
                if (ServiceModule.ServiceProviderProduct) {
                    bn.putString("UBERX_PARENT_CAT_ID", generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", userProfileJsonObj));
                } else {
                    bn.putString("UBERX_PARENT_CAT_ID", ServiceModule.RideDeliveryUbexProduct ? generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", userProfileJsonObj) : "");
                }
                new ActUtils(mContext).startActWithData(UploadDocTypeWiseActivity.class, bn);

            } else {
                if (ServiceModule.ServiceProviderProduct || ((ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) && eShowVehicles.equalsIgnoreCase("No"))) {
                    bn.putString("UBERX_PARENT_CAT_ID", generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", userProfileJsonObj));
                    (new ActUtils(getActivity())).startActWithData(UfxCategoryActivity.class, bn);
                } else if ((ServiceModule.RideDeliveryUbexProduct && isUfxServicesEnabled) && eShowVehicles.equalsIgnoreCase("Yes")) {
                    bn.putString("selView", "vehicle");
                    bn.putInt("totalVehicles", totalVehicles);

                    bn.putString("UBERX_PARENT_CAT_ID", ServiceModule.RideDeliveryProduct ? "" : generalFunc.getJsonValueStr("UBERX_PARENT_CAT_ID", userProfileJsonObj));

                    new ActUtils(mContext).startActWithData(UploadDocTypeWiseActivity.class, bn);
                } else {
                    if (totalVehicles > 0) {
                        new ActUtils(getActivity()).startActWithData(ManageVehiclesActivity.class, bn);
                    } else {
                        new ActUtils(getActivity()).startActWithData(AddVehicleActivity24.class, bn);
                    }
                }
            }
        } else if (position == 3) {
            new ActUtils(getActivity()).startAct(SetAvailabilityActivity.class);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        if (!isbtnClick) {
            getDriverStateDetails();
        }
    }

    private void getDriverStateDetails() {

        HashMap<String, String> parameters = new HashMap<>();
        parameters.put("type", "getDriverStates");
        parameters.put("iDriverId", generalFunc.getMemberId());
        parameters.put("UserType", Utils.app_type);

        ApiHandler.execute(mContext, parameters, isbtnClick, false, generalFunc, responseString -> {

            JSONObject responseStringObj = generalFunc.getJsonObject(responseString);
            SkeletonViewHandler.getInstance().hideSkeletonView();

            if (responseStringObj != null && !responseStringObj.toString().equals("")) {
                boolean isDataAvail = GeneralFunctions.checkDataAvail(Utils.action_str, responseStringObj);

                if (isDataAvail) {

                    isdocprogress = generalFunc.getJsonValueStr("IS_DOCUMENT_PROCESS_COMPLETED", responseStringObj).equalsIgnoreCase("yes");
                    isvehicleprogress = generalFunc.getJsonValueStr("IS_VEHICLE_PROCESS_COMPLETED", responseStringObj).equalsIgnoreCase("yes");
                    isServiceProcessComplete = generalFunc.getJsonValueStr("IS_SERVICE_PROCESS_COMPLETED", responseStringObj).equalsIgnoreCase("yes");

                    isdriveractive = generalFunc.getJsonValueStr("IS_DRIVER_STATE_ACTIVATED", responseStringObj).equalsIgnoreCase("yes");
                    isavailable = generalFunc.getJsonValueStr("IS_DRIVER_MANAGE_TIME_AVAILABLE", responseStringObj).equalsIgnoreCase("yes");

                    totalVehicles = GeneralFunctions.parseIntegerValue(0, generalFunc.getJsonValueStr("TotalVehicles", responseStringObj));

                    if (ServiceModule.ServiceProviderProduct) {
                        if (isdocprogress && isvehicleprogress && isdriveractive && isavailable) {
                            setData();
                            if (!isbtnClick) {
                                MyApp.getInstance().restartWithGetDataApp();
                                return;
                            } else {
                                handleDialog();
                                return;
                            }
                        }
                    } else {
                        if (isdocprogress && isvehicleprogress && isdriveractive) {
                            setData();
                            if (!isbtnClick) {
                                MyApp.getInstance().restartWithGetDataApp();
                                return;
                            } else {
                                handleDialog();
                                return;
                            }
                        }

                    }
                    if (isbtnClick) {
                        generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", "LBL_DRIVER_STATUS_INCOMPLETE"));
                        isbtnClick = false;
                    }
                    setData();

                } else {
                    generalFunc.showGeneralMessage("", generalFunc.retrieveLangLBl("", generalFunc.getJsonValueStr(Utils.message_str, responseStringObj)));
                }
            }
        });
    }

    private void handleDialog() {
        /*SuccessDialog.showSuccessDialog(getActivity(),
                generalFunc.retrieveLangLBl("Your Account Has Been Activated Successfully!", "LBL_DRIVER_STATUS_COMPLETE"),
                "", generalFunc.retrieveLangLBl("Ok", "LBL_OK"),
                false,
                () -> MyApp.getInstance().restartWithGetDataApp());*/

        CustomDialog customDialog = new CustomDialog(mContext);
        customDialog.setDetails(generalFunc.retrieveLangLBl("Your Account Has Been Activated Successfully!", "LBL_DRIVER_STATUS_COMPLETE"), "", generalFunc.retrieveLangLBl("Ok", "LBL_OK"), "", false, R.drawable.ic_correct_2, false, 1, true);
        customDialog.createDialog();
        customDialog.setPositiveButtonClick(() -> {
            MyApp.getInstance().restartWithGetDataApp();
        });
        customDialog.show();

    }

    public class setOnClickList implements View.OnClickListener {
        @Override
        public void onClick(View view) {
            int i = view.getId();
            if (i == submitBtnId) {
                isbtnClick = true;
                getDriverStateDetails();
            }
        }
    }
}