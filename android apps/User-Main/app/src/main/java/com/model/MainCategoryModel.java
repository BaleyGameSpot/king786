package com.model;

import java.util.ArrayList;
import java.util.HashMap;

public class MainCategoryModel implements Cloneable {

    String vTitle;
    String vDescription;
    String vCategoryImage;
    String iDisplayOrder;
    String iCategoryId;
    String isShowall;
    String eType;


    ViewType viewType;

    public enum ViewType {
        Header,
        Data
    }

    ArrayList<HashMap<String, String>> list;

    public String getIsShowall() {
        return isShowall;
    }

    public String geteType() {
        return eType;
    }

    public void seteType(String eType) {
        this.eType = eType;
    }

    public void setIsShowall(String isShowall) {
        this.isShowall = isShowall;
    }

    public String getiCategoryId() {
        return iCategoryId;
    }

    public void setiCategoryId(String iCategoryId) {
        this.iCategoryId = iCategoryId;
    }

    public String getvTitle() {
        return vTitle;
    }

    public void setvTitle(String vTitle) {
        this.vTitle = vTitle;
    }

    public String getvDescription() {
        return vDescription;
    }

    public void setvDescription(String vDescription) {
        this.vDescription = vDescription;
    }

    public String getvCategoryImage() {
        return vCategoryImage;
    }

    public void setvCategoryImage(String vCategoryImage) {
        this.vCategoryImage = vCategoryImage;
    }

    public String getiDisplayOrder() {
        return iDisplayOrder;
    }

    public void setiDisplayOrder(String iDisplayOrder) {
        this.iDisplayOrder = iDisplayOrder;
    }

    public ArrayList<HashMap<String, String>> getList() {
        return list;
    }

    public void setList(ArrayList<HashMap<String, String>> list) {
        this.list = list;
    }


    public ViewType getViewType() {
        return viewType;
    }

    public void setViewType(ViewType viewType) {
        this.viewType = viewType;
    }

    public Object clone() throws CloneNotSupportedException {
        return super.clone();
    }
}

