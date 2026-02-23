package com.data.models;

public class DataPreLoad {
    private static DataPreLoad instance;

    public enum DataType {
        GIFT_CARD,
        FOOD_RATING_DRIVER_FEEDBACK_QUESTIONS,
        CURRENCIES,
        LANGUAGES,
        TAXI_BID_INFO
    }

    public static DataPreLoad getInstance() {
        if (instance == null) {
            instance = new DataPreLoad();
        }
        return instance;
    }

    public void execute() {
        StaticData.getInstance().loadData();
    }

    public void retrieve(DataType dataType, DataHandler handler) {
        switch (dataType) {
            case GIFT_CARD:
            case FOOD_RATING_DRIVER_FEEDBACK_QUESTIONS:
            case LANGUAGES:
            case CURRENCIES:
            case TAXI_BID_INFO:
                StaticData.getInstance().retrieve(dataType, handler::onDataFound);
                break;
        }
    }

    public interface DataHandler {
        void onDataFound(Object dataObj);
    }
}