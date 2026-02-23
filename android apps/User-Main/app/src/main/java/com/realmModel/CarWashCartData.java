package com.realmModel;

import com.adapter.files.CategoryListItem;

import io.realm.RealmObject;

public class CarWashCartData extends RealmObject {

    String itemCount;
    String driverId;
    String SpecialInstruction;
    String finalTotal;
    String finalTotalNw;
    CategoryListItem CategoryListItem;
    String vSymbol;

    boolean isVideoConsultEnable = false;

    public boolean isVideoConsultEnable() {
        return isVideoConsultEnable;
    }

    public void setVideoConsultEnable(boolean videoConsultEnable) {
        isVideoConsultEnable = videoConsultEnable;
    }

    public String getvSymbol() {
        return vSymbol;
    }

    public void setvSymbol(String vSymbol) {
        this.vSymbol = vSymbol;
    }

    public String getItemCount() {
        return itemCount;
    }

    public void setItemCount(String itemCount) {
        this.itemCount = itemCount;
    }

    public String getDriverId() {
        return driverId;
    }

    public void setDriverId(String driverId) {
        this.driverId = driverId;
    }

    public String getSpecialInstruction() {
        return SpecialInstruction;
    }

    public void setSpecialInstruction(String specialInstruction) {
        SpecialInstruction = specialInstruction;
    }

    public String getFinalTotal() {
        return finalTotal;
    }

    public String getFinalTotalNw() {
        return finalTotalNw;
    }

    public void setFinalTotal(String finalTotal) {
        this.finalTotal = finalTotal;
    }

    public void setFinalTotalNw(String finalTotal) {
        this.finalTotalNw = finalTotal;
    }

    public CategoryListItem getCategoryListItem() {
        return CategoryListItem;
    }

    public void setCategoryListItem(CategoryListItem categoryListItem) {
        CategoryListItem = categoryListItem;
    }
}