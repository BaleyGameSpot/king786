package com.adapter.files;

public class CategoryListItem {

    private static final int ITEM = 0;
    private static final int SECTION = 1;

    private final int type;
    private final String text;

    private int sectionPosition;
    private int listPosition;
    private int CountSubItems;

    private String vTitle = "";
    private String iVehicleCategoryId = "";
    private String vCategory = "";
    private String vLogo = "";
    private String vBGColor = "";
    private String vLogo_TINT_color = "";

    private String eVideoConsultEnable = "";
    private String eVideoConsultEnableProvider = "";

    public CategoryListItem(int type, String text) {
        this.type = type;
        this.text = text;
    }

    public static int getITEM() {
        return ITEM;
    }

    public static int getSECTION() {
        return SECTION;
    }

    public int getType() {
        return type;
    }

    public String getText() {
        return text;
    }

    public int getSectionPosition() {
        return sectionPosition;
    }

    public void setSectionPosition(int sectionPosition) {
        this.sectionPosition = sectionPosition;
    }

    public int getListPosition() {
        return listPosition;
    }

    public String getvLogo_TINT_color() {
        return vLogo_TINT_color;
    }

    public void setvLogo_TINT_color(String vLogo_TINT_color) {
        this.vLogo_TINT_color = vLogo_TINT_color;
    }

    public void setListPosition(int listPosition) {
        this.listPosition = listPosition;
    }

    public int getCountSubItems() {
        return CountSubItems;
    }

    public void setCountSubItems(int countSubItems) {
        CountSubItems = countSubItems;
    }

    public String getvTitle() {
        return vTitle;
    }

    public void setvTitle(String vTitle) {
        this.vTitle = vTitle;
    }

    public String getiVehicleCategoryId() {
        return iVehicleCategoryId;
    }

    public void setiVehicleCategoryId(String iVehicleCategoryId) {
        this.iVehicleCategoryId = iVehicleCategoryId;
    }

    public String getvCategory() {
        return vCategory;
    }

    public void setvCategory(String vCategory) {
        this.vCategory = vCategory;
    }

    public String getvLogo() {
        return vLogo;
    }

    public void setvLogo(String vLogo) {
        this.vLogo = vLogo;
    }

    public String getvBGColor() {
        return vBGColor;
    }

    public void setvBGColor(String vBGColor) {
        this.vBGColor = vBGColor;
    }

    public String getVideoConsultEnable() {
        return eVideoConsultEnable;
    }

    public void setVideoConsultEnable(String eVideoConsultEnable) {
        this.eVideoConsultEnable = eVideoConsultEnable;
    }

    public String getVideoConsultEnableProvider() {
        return eVideoConsultEnableProvider;
    }

    public void setVideoConsultEnableProvider(String eVideoConsultEnableProvider) {
        this.eVideoConsultEnableProvider = eVideoConsultEnableProvider;
    }

    @Override
    public String toString() {
        return text;
    }
}


